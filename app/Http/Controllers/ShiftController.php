<?php

namespace App\Http\Controllers;

use App\Models\KasirShift;
use App\Models\PaymentRecord;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Audit;

class ShiftController extends Controller
{
    public function index()
    {
        $myOpen = KasirShift::openBy(auth()->id())->first();
        $recent = KasirShift::with('user')->orderByDesc('id')->paginate(15);

        return view('shift.index', compact('myOpen', 'recent'));
    }

    public function open(Request $r)
    {
        $data = $r->validate([
            'opening_cash' => ['required', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                // Cegah dua shift terbuka untuk user yang sama
                if (KasirShift::openBy(auth()->id())->lockForUpdate()->exists()) {
                    throw new \Exception('Anda masih punya shift terbuka. Tutup dulu shift sebelumnya.');
                }

                $shift = KasirShift::create([
                    'user_id'      => auth()->id(),
                    'opening_cash' => (int) $data['opening_cash'],
                    'notes'        => $data['notes'] ?? null,
                ]);

                // Audit log: buka shift
                Audit::log(
                    event: 'shift.opened',
                    subject: $shift,
                    description: 'Buka shift kasir',
                    properties: [
                        'opening_cash' => (int) $shift->opening_cash,
                    ]
                );
            });

            return back()->with('success', 'Shift dibuka.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function close(Request $r, KasirShift $shift)
    {
        // (Opsional) Batasi: hanya pemilik shift atau admin yang boleh tutup
        // if (auth()->user()->role !== 'admin' && $shift->user_id !== auth()->id()) {
        //     return back()->withErrors('Anda tidak berhak menutup shift ini.');
        // }

        if ($shift->status !== 'open') {
            return back()->withErrors('Shift sudah tertutup.');
        }

        $data = $r->validate([
            'closing_cash' => ['nullable', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string'],
            // hitung lembar uang (opsional)
            'denom'        => ['array'],
            'denom.*'      => ['nullable','integer','min:0'],
        ]);

        try {
            DB::transaction(function () use ($shift, $data) {
                // Kunci baris shift agar aman dari race condition
                $locked = KasirShift::whereKey($shift->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'open') {
                    throw new \Exception('Shift sudah tertutup.');
                }

                // expected = opening_cash + (cash in) - (cash out)
                $expectedDeltaSales = PaymentRecord::where('shift_id', $locked->id)
                    ->where('method', 'cash')
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0)
                      - COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) as val
                    ")
                    ->value('val');
                $expectedDeltaOps = CashMovement::where('shift_id', $locked->id)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0)
                      - COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) as val
                    ")->value('val');

                $expected = (int) $locked->opening_cash + (int) $expectedDeltaSales + (int) $expectedDeltaOps;
                // Hitung closing dari lembar uang jika diisi
                $denoms = $data['denom'] ?? [];
                $closing = null;
                $useDenom = false;
                if (is_array($denoms) && count($denoms)) {
                    foreach ($denoms as $qty) { if (((int)$qty) > 0) { $useDenom = true; break; } }
                    if ($useDenom) {
                        $sum = 0; foreach ($denoms as $nom => $qty) { $sum += ((int)$nom) * max(0, (int)$qty); }
                        $closing = $sum;
                    }
                }
                if ($closing === null) {
                    // gunakan input manual hanya jika diisi
                    $closing = isset($data['closing_cash']) && $data['closing_cash'] !== ''
                        ? (int)$data['closing_cash']
                        : null;
                }
                $diff     = (int) $closing - $expected;

                // breakdown per metode pembayaran
                $byMethod = PaymentRecord::where('shift_id', $locked->id)
                    ->selectRaw("method, SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                    ->groupBy('method')
                    ->get()
                    ->mapWithKeys(fn($r)=>[$r->method => ['in'=>(int)$r->masuk,'out'=>(int)$r->keluar]])
                    ->toArray();
                $ops = CashMovement::where('shift_id',$locked->id)
                    ->selectRaw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                    ->first();
                $byMethod['_cash_ops'] = ['in'=>(int)($ops->masuk ?? 0), 'out'=>(int)($ops->keluar ?? 0)];

                $locked->update([
                    'closed_at'     => now(),
                    'closing_cash'  => $closing !== null ? (int)$closing : null,
                    'expected_cash' => $expected,
                    'difference'    => $diff,
                    'status'        => 'closed',
                    'notes'         => $data['notes'] ?? $locked->notes,
                    'cash_count'    => $denoms ? json_encode($denoms) : $locked->cash_count,
                    'method_breakdown' => json_encode($byMethod),
                ]);

                // Audit log: tutup shift
                Audit::log(
                    event: 'shift.closed',
                    subject: $locked,
                    description: 'Tutup shift kasir',
                    properties: [
                        'opening_cash' => (int) $locked->opening_cash,
                        'expected'     => (int) $locked->expected_cash,
                        'closing_cash' => (int) $locked->closing_cash,
                        'difference'   => (int) $locked->difference,
                        'cash_count'   => $denoms,
                        'method_breakdown' => $byMethod,
                    ]
                );
            });

            return back()->with('success', 'Shift ditutup.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // Kas Masuk (tambah uang ke laci, non-transaksi)
    public function cashIn(Request $r)
    {
        $data = $r->validate([
            'amount'    => ['required','integer','min:1'],
            'reference' => ['nullable','string','max:100'],
            'note'      => ['nullable','string','max:255'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $shiftId = KasirShift::openBy(auth()->id())->lockForUpdate()->value('id');
                if (!$shiftId) throw new \Exception('Shift belum dibuka.');

                $rec = CashMovement::create([
                    'shift_id'   => $shiftId,
                    'direction'  => 'in',
                    'amount'     => (int)$data['amount'],
                    'reference'  => $data['reference'] ?? null,
                    'note'       => $data['note'] ?? 'Kas masuk (operasional)',
                    'occurred_at'=> now(),
                    'created_by' => auth()->id(),
                ]);

                Audit::log('cash.in', $rec, 'Kas masuk (operasional)', [
                    'amount'    => (int)$rec->amount,
                    'reference' => $rec->reference,
                    'note'      => $rec->note,
                    'shift_id'  => $rec->shift_id,
                ]);
            });

            return back()->with('success', 'Kas masuk dicatat.');
        } catch (\Throwable $e) {
            report($e); return back()->withErrors($e->getMessage());
        }
    }

    // Kas Keluar (biaya operasional, setor, dll non-transaksi)
    public function cashOut(Request $r)
    {
        $data = $r->validate([
            'amount'    => ['required','integer','min:1'],
            'reference' => ['nullable','string','max:100'],
            'note'      => ['nullable','string','max:255'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $shiftId = KasirShift::openBy(auth()->id())->lockForUpdate()->value('id');
                if (!$shiftId) throw new \Exception('Shift belum dibuka.');

                $rec = CashMovement::create([
                    'shift_id'   => $shiftId,
                    'direction'  => 'out',
                    'amount'     => (int)$data['amount'],
                    'reference'  => $data['reference'] ?? null,
                    'note'       => $data['note'] ?? 'Kas keluar (operasional)',
                    'occurred_at'=> now(),
                    'created_by' => auth()->id(),
                ]);

                Audit::log('cash.out', $rec, 'Kas keluar (operasional)', [
                    'amount'    => (int)$rec->amount,
                    'reference' => $rec->reference,
                    'note'      => $rec->note,
                    'shift_id'  => $rec->shift_id,
                ]);
            });

            return back()->with('success', 'Kas keluar dicatat.');
        } catch (\Throwable $e) {
            report($e); return back()->withErrors($e->getMessage());
        }
    }
}
