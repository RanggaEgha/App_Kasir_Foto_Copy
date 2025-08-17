<?php

namespace App\Http\Controllers;

use App\Models\KasirShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $myOpen  = KasirShift::openBy(auth()->id())->first();
        $recent  = KasirShift::with('user')->orderByDesc('id')->paginate(15);
        return view('shift.index', compact('myOpen', 'recent'));
    }

    public function open(Request $r)
    {
        $data = $r->validate([
            'opening_cash' => ['required','integer','min:0'],
            'notes'        => ['nullable','string'],
        ]);

        if (KasirShift::openBy(auth()->id())->exists()) {
            return back()->withErrors('Anda masih punya shift terbuka. Tutup dulu shift sebelumnya.');
        }

        KasirShift::create([
            'user_id'      => auth()->id(),
            'opening_cash' => (int)$data['opening_cash'],
            'notes'        => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Shift dibuka.');
    }

   public function close(Request $r, \App\Models\KasirShift $shift)
{
    if ($shift->status !== 'open') {
        return back()->withErrors('Shift sudah tertutup.');
    }
    $data = $r->validate([
        'closing_cash' => ['required','integer','min:0'],
        'notes'        => ['nullable','string'],
    ]);

    // expected = opening_cash + (cash in) - (cash out)
    $expectedDelta = \App\Models\PaymentRecord::where('shift_id', $shift->id)
        ->where('method', 'cash')
        ->selectRaw("
            COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE 0 END),0)
          - COALESCE(SUM(CASE WHEN direction='out' THEN amount ELSE 0 END),0) as val
        ")->value('val');

    $expected = (int)$shift->opening_cash + (int)$expectedDelta;
    $diff = (int)$data['closing_cash'] - $expected;

    $shift->update([
        'closed_at'     => now(),
        'closing_cash'  => (int)$data['closing_cash'],
        'expected_cash' => $expected,
        'difference'    => $diff,
        'status'        => 'closed',
        'notes'         => $data['notes'] ?? $shift->notes,
    ]);

    return back()->with('success','Shift ditutup.');
}

}
