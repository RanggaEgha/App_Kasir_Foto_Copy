<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BarangController extends Controller
{
   public function index(Request $request)
{
    $perPage = 15;

    $query = Barang::with(['units' => function ($q) {
        $q->orderBy('kode'); // biar urut PCS, PAKET, BOX, dst
    }])->latest();

    $barangs = $query->paginate($perPage)->withQueryString();

    // Guard: kalau ?page di luar rentang, redirect ke halaman valid
    $page = max(1, (int) $request->query('page', 1));
    if ($page > 1 && $page > $barangs->lastPage()) {
        return redirect()->route('barang.index', ['page' => $barangs->lastPage()]);
    }

    return view('barang.index', compact('barangs'));
}

    public function create()
    {
        $units = Unit::orderBy('kode')->get();
        return view('barang.create', compact('units'));
    }

    public function store(Request $request)
    {
        // Validasi dasar + akan ditambah cek harga per unit yang dipilih
        $validator = Validator::make($request->all(), [
            'nama'       => ['required', 'string', 'max:255'],
            'kategori'   => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'units'      => ['array'],
            'units.*'    => ['string'],
            'stok'       => ['array'],
            'stok.*'     => ['nullable','integer','min:0'],
            'harga'      => ['array'],
            'harga.*'    => ['nullable','numeric','min:0'],
        ]);

        $validator->after(function($v) use ($request){
            $unitsSel = (array) $request->input('units', []);
            $harga    = (array) $request->input('harga', []);
            $stok     = (array) $request->input('stok',  []);
            foreach ($unitsSel as $uid) {
                $uid = (string) $uid;
                $valHarga = $harga[$uid] ?? null;
                if ($valHarga === '' || $valHarga === null || !is_numeric($valHarga)) {
                    $v->errors()->add('harga.'.$uid, 'Harga untuk unit yang dipilih wajib diisi.');
                }
                $valStok = $stok[$uid] ?? null;
                if ($valStok === '' || $valStok === null || !is_numeric($valStok)) {
                    $v->errors()->add('stok.'.$uid, 'Stok untuk unit yang dipilih wajib diisi.');
                }
            }
        });

        if (!$validator->passes()) {
            // Cache gambar sementara agar tidak hilang setelah redirect
            $tmp = null;
            if ($request->hasFile('image')) {
                $tmp = $request->file('image')->store('tmp/barang', 'public');
            } elseif ($request->filled('temp_image')) {
                $tmp = $request->input('temp_image');
            }

            if ($tmp) {
                $request->merge(['temp_image' => $tmp]);
            }

            return back()->withErrors($validator)
                ->withInput()
                ->with('temp_image_path', $tmp);
        }
        $data = $validator->validated();

        DB::transaction(function () use ($request, $data) {
            $barang = Barang::create($data);

            // === Upload gambar (opsional) ===
            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                ]);
                $path = $request->file('image')->store('barang', 'public');
                $barang->update(['image_path' => $path]);
            } elseif ($request->filled('temp_image') && Storage::disk('public')->exists($request->input('temp_image'))) {
                $temp = $request->input('temp_image');
                $newPath = 'barang/'.basename($temp);
                Storage::disk('public')->move($temp, $newPath);
                $barang->update(['image_path' => $newPath]);
            }

            // === Sinkronisasi unit, stok, harga (pivot) ===
            $unitsSel = (array) $data['units'] ?? [];
            $stok     = (array) $request->input('stok', []);
            $harga    = (array) $request->input('harga', []);

            $attach = [];
            foreach ($unitsSel as $uid) {
                $uid = (string) $uid;
                $attach[$uid] = [
                    'stok'  => is_numeric($stok[$uid] ?? null) ? (int) $stok[$uid] : 0,
                    'harga' => (int) ($harga[$uid] ?? 0),
                ];
            }
            $barang->units()->sync($attach);

            // === Opsional: sinkronkan kolom legacy untuk kompatibilitas (pcs/paket) ===
            $this->syncLegacyColumns($barang);
        });

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        $barang->load('units');
        $units = Unit::orderBy('kode')->get();
        return view('barang.edit', compact('barang', 'units'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validator = Validator::make($request->all(), [
            'nama'       => ['required', 'string', 'max:255'],
            'kategori'   => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'units'      => ['array'],
            'units.*'    => ['string'],
            'stok'       => ['array'],
            'stok.*'     => ['nullable','integer','min:0'],
            'harga'      => ['array'],
            'harga.*'    => ['nullable','numeric','min:0'],
        ]);

        $validator->after(function($v) use ($request){
            $unitsSel = (array) $request->input('units', []);
            $harga    = (array) $request->input('harga', []);
            $stok     = (array) $request->input('stok',  []);
            foreach ($unitsSel as $uid) {
                $uid = (string) $uid;
                $valHarga = $harga[$uid] ?? null;
                if ($valHarga === '' || $valHarga === null || !is_numeric($valHarga)) {
                    $v->errors()->add('harga.'.$uid, 'Harga untuk unit yang dipilih wajib diisi.');
                }
                $valStok = $stok[$uid] ?? null;
                if ($valStok === '' || $valStok === null || !is_numeric($valStok)) {
                    $v->errors()->add('stok.'.$uid, 'Stok untuk unit yang dipilih wajib diisi.');
                }
            }
        });

        if (!$validator->passes()) {
            $tmp = null;
            if ($request->hasFile('image')) {
                $tmp = $request->file('image')->store('tmp/barang', 'public');
            } elseif ($request->filled('temp_image')) {
                $tmp = $request->input('temp_image');
            }

            if ($tmp) {
                $request->merge(['temp_image' => $tmp]);
            }

            return back()->withErrors($validator)
                ->withInput()
                ->with('temp_image_path', $tmp);
        }
        $data = $validator->validated();

        DB::transaction(function () use ($request, $barang, $data) {
            $barang->update($data);

            // === Ganti gambar (opsional) ===
            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                ]);
                $old = $barang->image_path;
                $path = $request->file('image')->store('barang', 'public');
                $barang->update(['image_path' => $path]);

                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            } elseif ($request->filled('temp_image') && Storage::disk('public')->exists($request->input('temp_image'))) {
                // Pakai gambar sementara jika ada
                $old = $barang->image_path;
                $temp = $request->input('temp_image');
                $newPath = 'barang/'.basename($temp);
                Storage::disk('public')->move($temp, $newPath);
                $barang->update(['image_path' => $newPath]);
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            // === Sinkronisasi pivot ===
            $unitsSel = (array) $data['units'] ?? [];
            $stok     = (array) $request->input('stok', []);
            $harga    = (array) $request->input('harga', []);

            $sync = [];
            foreach ($unitsSel as $uid) {
                $uid = (string) $uid;
                $sync[$uid] = [
                    'stok'  => is_numeric($stok[$uid] ?? null) ? (int) $stok[$uid] : 0,
                    'harga' => (int) ($harga[$uid] ?? 0),
                ];
            }
            $barang->units()->sync($sync);

            // === Opsional: sinkronkan kolom legacy (pcs/paket)
            $this->syncLegacyColumns($barang);
        });

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        DB::transaction(function () use ($barang) {
            if ($barang->image_path && Storage::disk('public')->exists($barang->image_path)) {
                Storage::disk('public')->delete($barang->image_path);
            }
            $barang->units()->detach();
            $barang->delete();
        });

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * Sinkronkan kolom legacy (harga_satuan/stok_satuan, harga_paket/stok_paket, isi_per_paket)
     * supaya modul lama yang masih pakai kolom-kolom ini tetap aman.
     */
    private function syncLegacyColumns(Barang $barang): void
    {
        $barang->loadMissing('units');
        $byCode = $barang->units->keyBy(fn($u) => strtolower($u->kode));

        $pcs   = $byCode->get('pcs');
        $paket = $byCode->get('paket');

        $updates = [];

        if ($pcs) {
            $updates['harga_satuan'] = $pcs->pivot?->harga;
            $updates['stok_satuan']  = $pcs->pivot?->stok;
        }

        if ($paket) {
            $updates['harga_paket']  = $paket->pivot?->harga;
            $updates['stok_paket']   = $paket->pivot?->stok;
            // kalau master paket punya konversi → simpan sebagai isi_per_paket
            $updates['isi_per_paket'] = $paket->konversi ?? $barang->isi_per_paket;
        }

        if (!empty($updates)) {
            $barang->update($updates);
        }
    }
}
