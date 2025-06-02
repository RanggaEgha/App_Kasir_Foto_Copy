<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jasa;

class JasaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
    $jasas = Jasa::all();
    return view('jasa.index', compact('jasas'));
}

public function create() {
    return view('jasa.create');
}

public function store(Request $request) {
     $request->validate([
        'nama' => 'required|string|max:255',
        'jenis' => 'nullable|string|max:255',
        'satuan' => 'required|string|max:100',
        'harga_per_satuan' => 'required|integer',
        'keterangan' => 'nullable|string',
    ]);

    Jasa::create($request->all());

    return redirect()->route('jasa.index')->with('success', 'Jasa berhasil ditambahkan.');
}

public function edit($id) {
    $jasa = Jasa::findOrFail($id);
    return view('jasa.edit', compact('jasa'));
}

public function update(Request $request, $id) {
    $request->validate([
        'nama' => 'required',
        'harga' => 'required|integer',
    ]);
    $jasa = Jasa::findOrFail($id);
    $jasa->update($request->all());
    return redirect()->route('jasa.index')->with('success', 'Jasa berhasil diperbarui!');
}

public function destroy($id) {
    $jasa = Jasa::findOrFail($id);
    $jasa->delete();
    return back()->with('success', 'Jasa berhasil dihapus.');
}

}
