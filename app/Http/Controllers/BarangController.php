<?php
namespace App\Http\Controllers;

use App\Models\{Barang, Unit};
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /* ---------- LIST ---------- */
    public function index()
    {
        $barangs = Barang::with('units')->latest()->paginate(15);
        return view('barang.index', compact('barangs'));
    }

    /* ---------- CREATE ---------- */
    public function create()
    {
        $units = Unit::all();                 // pcs | pack | lusin
        return view('barang.create', compact('units'));
    }

    /* ---------- STORE ---------- */
    public function store(Request $r)
    {
        $data = $r->validate([
            'nama'      => 'required|string|max:100',
            'kategori'  => 'nullable|string|max:50',
            'keterangan'=> 'nullable|string',
            'units'     => 'required|array|min:1',
            'units.*'   => 'exists:units,id',
            'stok'      => 'required|array',
            'harga'     => 'required|array',
        ]);

        $barang = Barang::create($data);

        /* simpan pivot */
        foreach ($data['units'] as $unitId) {
            $barang->units()->attach($unitId, [
                'stok' => $data['stok'][$unitId] ?? 0,
                'harga'=> $data['harga'][$unitId] ?? 0,
            ]);
        }
        return to_route('barang.index')->with('success','Barang ditambahkan');
    }

    /* ---------- EDIT ---------- */
    public function edit(Barang $barang)
    {
        $barang->load('units');
        $units = Unit::all();
        return view('barang.edit', compact('barang','units'));
    }

    /* ---------- UPDATE ---------- */
    public function update(Request $r, Barang $barang)
    {
        $data = $r->validate([
            'nama'      => 'required|string|max:100',
            'kategori'  => 'nullable|string|max:50',
            'keterangan'=> 'nullable|string',
            'units'     => 'required|array|min:1',
            'units.*'   => 'exists:units,id',
            'stok'      => 'required|array',
            'harga'     => 'required|array',
        ]);

        $barang->update($data);

        /* sinkron pivot (detach & attach ulang) */
        $sync = [];
        foreach ($data['units'] as $unitId) {
            $sync[$unitId] = [
                'stok'  => $data['stok'][$unitId] ?? 0,
                'harga' => $data['harga'][$unitId] ?? 0,
            ];
        }
        $barang->units()->sync($sync);

        return to_route('barang.index')->with('success','Barang diperbarui');
    }

    /* ---------- DELETE ---------- */
    public function destroy(Barang $barang)
    {
        $barang->delete();
        return back()->with('success','Barang dihapus');
    }
}
