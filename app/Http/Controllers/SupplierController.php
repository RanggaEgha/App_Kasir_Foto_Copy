<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /* ───── Tampilkan tabel ───── */
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /* ───── Form tambah ───── */
    public function create()
    {
        return view('suppliers.create');
    }

    /* ───── Simpan data ───── */
    public function store(Request $r)
    {
        Supplier::create($r->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string',
            'phone'          => 'nullable|string',
            'email'          => 'nullable|email',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
        ]));

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier berhasil ditambahkan');
    }

    /* ───── Form edit ───── */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /* ───── Update data ───── */
    public function update(Request $r, Supplier $supplier)
    {
        $supplier->update($r->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string',
            'phone'          => 'nullable|string',
            'email'          => 'nullable|email',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
        ]));

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier berhasil diperbarui');
    }

    /* ───── Hapus data ───── */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Supplier dihapus');
    }
}
