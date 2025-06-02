<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Barang, Unit};

class BarangController extends Controller
{
    /* ─────────────────────────────  LIST  ───────────────────────────── */
    public function index()
    {
        $barangs = Barang::latest()->paginate(15);
        return view('barang.index', compact('barangs'));
    }

    /* ───────────────────────────── CREATE ───────────────────────────── */
    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $r)
    {
        $r->validate([
            'nama'         => 'required|string|max:100',
            'jenis'        => 'nullable|string|max:50',
            'satuan'       => 'required|string|max:20',
            'harga_satuan' => 'required|integer|min:0',
        ]);

        Barang::create($r->only('nama','jenis','satuan','harga_satuan'));
        return redirect()->route('barang.index')
                         ->with('success', 'Barang tersimpan');
    }

    /* ───────────────────────────── SHOW ─────────────────────────────── */
    public function show($id)
    {
        $barang = Barang::with('units')->findOrFail($id);
        return view('barang.show', compact('barang'));
    }

    /* ───────────────────────────── EDIT ─────────────────────────────── */
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'nama'         => 'required|string|max:100',
            'jenis'        => 'nullable|string|max:50',
            'satuan'       => 'required|string|max:20',
            'harga_satuan' => 'required|integer|min:0',
        ]);

        Barang::findOrFail($id)
              ->update($r->only('nama','jenis','satuan','harga_satuan'));

        return redirect()->route('barang.index')
                         ->with('success', 'Barang diperbarui');
    }

    /* ─────────────────────────── DELETE ─────────────────────────────── */
    public function destroy($id)
    {
        Barang::findOrFail($id)->delete();
        return back()->with('success', 'Barang dihapus');
    }

    /* ────────────────────── KELOLA UNIT & HARGA ─────────────────────── */

    /** Form kelola unit-harga-stok */
    public function editUnits($id)
    {
        return view('barang.edit_units', [
            'barang' => Barang::with('units')->findOrFail($id),
            'units'  => Unit::all(),          // daftar unit yg bisa dipilih
        ]);
    }

    /** Simpan harga & stok per-unit */
    public function updateUnits(Request $r, $id)
    {
        $barang = Barang::findOrFail($id);

        $sync = [];                   // idUnit => ['harga'=>..,'stok'=>..]
        foreach ($r->units ?? [] as $uid => $row) {
            $sync[$uid] = [
                'harga' => (int) $row['harga'],
                'stok'  => (int) $row['stok'],
            ];
        }

        DB::transaction(fn() => $barang->units()->sync($sync));

        return back()->with('success', 'Harga & stok per-unit tersimpan');
    }
}
