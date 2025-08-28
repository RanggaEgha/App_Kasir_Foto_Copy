<?php

namespace App\Http\Controllers;

use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
        $validator = Validator::make($request->all(), [
            'nama'             => ['required','string','max:255'],
            'jenis'            => ['nullable','string','max:255'],
            'satuan'           => ['required','string','max:100'],
            'harga_per_satuan' => ['required','integer','min:0'],
            'keterangan'       => ['nullable','string'],
            'image'            => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        if (!$validator->passes()) {
            $tmp = null;
            if ($request->hasFile('image')) {
                $tmp = $request->file('image')->store('tmp/jasa', 'public');
            } elseif ($request->filled('temp_image')) {
                $tmp = $request->input('temp_image');
            }
            if ($tmp) { $request->merge(['temp_image' => $tmp]); }
            return back()->withErrors($validator)->withInput()->with('temp_image_path', $tmp);
        }

        $data = $validator->validated();
        $jasa = Jasa::create($data);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('jasa', 'public');
            $jasa->update(['image_path' => $path]);
        } elseif ($request->filled('temp_image') && Storage::disk('public')->exists($request->input('temp_image'))) {
            $temp = $request->input('temp_image');
            $newPath = 'jasa/'.basename($temp);
            Storage::disk('public')->move($temp, $newPath);
            $jasa->update(['image_path' => $newPath]);
        }

        return redirect()->route('jasa.index')->with('success', 'Jasa berhasil ditambahkan.');
    }

    /* ---------- UPDATE ---------- */
    public function edit($id)
    {
        $jasa = Jasa::findOrFail($id);
        return view('jasa.edit', compact('jasa'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama'             => ['required','string','max:255'],
            'jenis'            => ['nullable','string','max:255'],
            'satuan'           => ['required','string','max:100'],
            'harga_per_satuan' => ['required','integer','min:0'],
            'keterangan'       => ['nullable','string'],
            'image'            => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        if (!$validator->passes()) {
            $tmp = null;
            if ($request->hasFile('image')) {
                $tmp = $request->file('image')->store('tmp/jasa', 'public');
            } elseif ($request->filled('temp_image')) {
                $tmp = $request->input('temp_image');
            }
            if ($tmp) { $request->merge(['temp_image' => $tmp]); }
            return back()->withErrors($validator)->withInput()->with('temp_image_path', $tmp);
        }

        $data = $validator->validated();
        $jasa = Jasa::findOrFail($id);
        $jasa->update($data);

        if ($request->hasFile('image')) {
            $old = $jasa->image_path;
            $path = $request->file('image')->store('jasa', 'public');
            $jasa->update(['image_path' => $path]);
            if ($old && Storage::disk('public')->exists($old)) { Storage::disk('public')->delete($old); }
        } elseif ($request->filled('temp_image') && Storage::disk('public')->exists($request->input('temp_image'))) {
            $old = $jasa->image_path;
            $temp = $request->input('temp_image');
            $newPath = 'jasa/'.basename($temp);
            Storage::disk('public')->move($temp, $newPath);
            $jasa->update(['image_path' => $newPath]);
            if ($old && Storage::disk('public')->exists($old)) { Storage::disk('public')->delete($old); }
        }

        return redirect()->route('jasa.index')->with('success', 'Jasa berhasil diperbarui!');
    }

    /* ---------- DELETE ---------- */
    public function destroy($id)
    {
        $jasa = Jasa::findOrFail($id);
        $old = $jasa->image_path;
        $jasa->delete();
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
        return back()->with('success', 'Jasa berhasil dihapus.');
    }
}
