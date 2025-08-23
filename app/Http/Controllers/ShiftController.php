<?php

namespace App\Http\Controllers;

use App\Models\KasirShift;
use App\Models\PaymentRecord;
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
            'closing_cash' => ['required', 'integer', 'min:0'],
            'notes'        => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($shift, $data) {
                // Kunci baris shift agar aman dari race condition
                $locked = KasirShift::whereKey($shift->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'open') {
                    throw new \Exception('Shift sudah tertutup.');
                }

                // expected = opening_cash + (cash in) - (cash out)
                $expectedDelta = PaymentRecord::where('shift_id', $locked->id)
                    ->where('method', 'cash')
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0)
                      - COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) as val
                    ")
                    ->value('val');

                $expected = (int) $locked->opening_cash + (int) $expectedDelta;
                $diff     = (int) $data['closing_cash'] - $expected;

                $locked->update([
                    'closed_at'     => now(),
                    'closing_cash'  => (int) $data['closing_cash'],
                    'expected_cash' => $expected,
                    'difference'    => $diff,
                    'status'        => 'closed',
                    'notes'         => $data['notes'] ?? $locked->notes,
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
                    ]
                );
            });

            return back()->with('success', 'Shift ditutup.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }
}
