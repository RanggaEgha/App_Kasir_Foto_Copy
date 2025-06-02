<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::all();
        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'kategori' => 'required',
            'satuan' => 'required',
            'stok_satuan' => 'required|numeric',
            'stok_paket' => 'required|numeric',
            'isi_per_paket' => 'nullable|numeric',
            'harga_satuan' => 'required|numeric',
            'harga_paket' => 'nullable|numeric',
        ]);

        Barang::findOrFail($id)->update($request->all());

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

     public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return view('barang.edit', compact('barang'));
    }



      public function destroy($id)
    {
        Barang::destroy($id);
        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required',
        'tipe_penjualan' => 'required',
        'harga_satuan' => 'nullable|integer',
        'harga_paket' => 'nullable|integer',
        'isi_per_paket' => 'nullable|integer',
        'stok_satuan' => 'nullable|integer',
        'stok_paket' => 'nullable|integer',
    ]);

    Barang::create($request->all());

    return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
}

}
