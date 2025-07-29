<?php

namespace App\Http\Controllers;

use App\Models\Jasa;
use Illuminate\Http\Request;

class JasaController extends Controller
{
    /* ---------- READ ---------- */
    public function index()
    {
        $jasas = Jasa::all();
        return view('jasa.index', compact('jasas'));
    }

    /* ---------- CREATE ---------- */
    public function create()
    {
        return view('jasa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'jenis'            => 'nullable|string|max:255',
            'satuan'           => 'required|string|max:100',
            'harga_per_satuan' => 'required|integer|min:0',
            'keterangan'       => 'nullable|string',
        ]);

        Jasa::create($validated);

        return redirect()
            ->route('jasa.index')
            ->with('success', 'Jasa berhasil ditambahkan.');
    }

    /* ---------- UPDATE ---------- */
    public function edit($id)
    {
        $jasa = Jasa::findOrFail($id);
        return view('jasa.edit', compact('jasa'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'jenis'            => 'nullable|string|max:255',
            'satuan'           => 'required|string|max:100',
            'harga_per_satuan' => 'required|integer|min:0',
            'keterangan'       => 'nullable|string',
        ]);

        Jasa::findOrFail($id)->update($validated);

        return redirect()
            ->route('jasa.index')
            ->with('success', 'Jasa berhasil diperbarui!');
    }

    /* ---------- DELETE ---------- */
    public function destroy($id)
    {
        Jasa::findOrFail($id)->delete();
        return back()->with('success', 'Jasa berhasil dihapus.');
    }
}
