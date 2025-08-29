@extends('layouts.app')
@section('title','Detail Aktivitas')

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  // -------- Normalisasi ----------
  $props = is_array($auditLog->properties) ? $auditLog->properties : (json_decode($auditLog->properties ?? '[]', true) ?: []);
  $old   = is_array($auditLog->old_values) ? $auditLog->old_values : (json_decode($auditLog->old_values ?? '[]', true) ?: []);
  $new   = is_array($auditLog->new_values) ? $auditLog->new_values : (json_decode($auditLog->new_values ?? '[]', true) ?: []);
  $media = isset($props['media_changes']) && is_array($props['media_changes']) ? $props['media_changes'] : [];

  // -------- Helpers ----------
  function baseType($t){ return class_basename($t ?? ''); }

  function rupiah($v){
    if ($v === null || $v === '' || $v === '—') return '—';
    if (!is_numeric($v)) return (string)$v;
    return 'Rp'.number_format((float)$v,0,',','.');
  }

  function prettyLabel($k){
    return match($k){
      // umum
      'nama' => 'Nama',
      'kategori' => 'Kategori',
      'keterangan' => 'Keterangan',
      'image_path','image','photo','thumbnail','gambar' => 'Gambar',
      // barang
      'stok','stok_pcs' => 'Stok',
      'harga','harga_satuan' => 'Harga',
      'harga_paket' => 'Harga Paket',
      // transaksi/pembelian
      'status' => 'Status',
      'payment_status' => 'Status Pembayaran',
      'total_harga' => 'Total',
      'dibayar' => 'Dibayar',
      'kembalian' => 'Kembalian',
      'metode_bayar' => 'Metode Bayar',
      'discount_type' => 'Tipe Diskon',
      'discount_value'=> 'Nilai Diskon',
      'discount_amount'=> 'Total Diskon',
      'discount_reason'=> 'Alasan Diskon',
      'coupon_code'    => 'Kupon',
      'invoice_no' => 'No. Invoice',
      'grand_total' => 'Grand Total',
      'tanggal' => 'Tanggal',
      // user
      'name' => 'Nama',
      'email' => 'Email',
      'role' => 'Peran',
      'is_active' => 'Aktif',
      default => Str::title(str_replace(['_','id'],' ', $k)),
    };
  }

  function isMoneyField($k){
    return in_array($k, [
      'harga','harga_satuan','harga_paket','total_harga','dibayar','kembalian','grand_total','unit_price','subtotal'
    ], true);
  }

  function fmtVal($k,$v){
    if ($v === null || $v === '') return '—';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (isMoneyField($k)) return rupiah($v);
    if (is_array($v) || is_object($v)) return Str::limit(json_encode($v, JSON_UNESCAPED_UNICODE), 80);
    return (string)$v;
  }

  /** Nama objek yang dibaca manusia (tanpa ID & tanpa tautan) */
  function subjectName($log){
    $type = baseType($log->subject_type);
    $id   = $log->subject_id;

    $map = [
      'Barang'         => [\App\Models\Barang::class, 'nama',       'Barang'],
      'Jasa'           => [\App\Models\Jasa::class,   'nama',       'Jasa'],
      'User'           => [\App\Models\User::class,   'name',       'Pengguna'],
      'Supplier'       => [\App\Models\Supplier::class,'name',      'Supplier'],
      'PurchaseOrder'  => [\App\Models\PurchaseOrder::class,'invoice_no','Pembelian'],
      'Transaksi'      => [\App\Models\Transaksi::class,'kode_transaksi','Transaksi'],
    ];

    $jenis = $type;
    $nama  = $type;

    if(isset($map[$type])){
      [$cls,$nameCol,$jenisMap] = $map[$type];
      $jenis = $jenisMap;
      try {
        $m = $cls::find($id);
        if($m){
          $val = $m->{$nameCol} ?? null;
          if($val) $nama = $val;
        }
      } catch (\Throwable $e) {}
    }
    return [$jenis, $nama];
  }

  /** Ubah path relatif jadi URL storage; jika file tidak ada → null */
  function imageUrlOrNull($path){
    if(!$path || $path === 'old' || $path === 'new') return null; // guard nilai aneh
    // jika sudah http/https atau /storage/
    if (preg_match('~^https?://~i', $path) || Str::startsWith($path, ['/storage/','storage/'])) {
      return $path;
    }
    // cek keberadaan file di disk "public"
    try {
      if (Storage::disk('public')->exists($path)) {
        return Storage::url($path); // menghasilkan /storage/{path}
      }
    } catch (\Throwable $e) {}
    return null;
  }

  // -------- Build daftar perubahan (diffs) --------
  $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
  $diffs = [];
  foreach($keys as $k){
    $o = $old[$k] ?? null;
    $n = $new[$k] ?? null;
    if ($o === $n) continue;
    $diffs[] = [
      'k' => $k,
      'label' => prettyLabel($k),
      'o' => fmtVal($k,$o),
      'n' => fmtVal($k,$n),
      '_raw_o' => $o,
      '_raw_n' => $n,
    ];
  }

  // Gambar dari media_changes atau fallback dari field image_*
  $imageKeys = ['image_path','image','photo','thumbnail','gambar'];
  $fallbackImageDiffs = array_values(array_filter($diffs, fn($d)=> in_array(strtolower($d['k']), $imageKeys, true)));

  // -------- Ringkasan kalimat sederhana untuk orang awam --------
  [$jenis, $namaObjek] = subjectName($auditLog);
  $actor = trim(($auditLog->actor_name ?? 'Pengguna').' '.($auditLog->actor_role ? "({$auditLog->actor_role})" : ''));

  // pilih perubahan yang paling relevan untuk kalimat
  $cari = function($keys) use ($diffs){
    foreach($diffs as $d){ if(in_array($d['k'],$keys,true)) return $d; }
    return null;
  };

  $ringkas = null;
  $ev = strtolower((string)$auditLog->event);

  if ($ev === 'created') {
    $ringkas = "{$actor} menambahkan {$jenis} {$namaObjek}.";
  } elseif ($ev === 'deleted') {
    $ringkas = "{$actor} menghapus {$jenis} {$namaObjek}.";
  } else {
    // stok
    $stok = $cari(['stok_pcs','stok']);
    if($stok){
      $ringkas = "{$actor} mengubah stok {$jenis} {$namaObjek} dari {$stok['o']} menjadi {$stok['n']}.";
    }
    // harga
    if(!$ringkas){
      $harga = $cari(['harga','harga_satuan','harga_paket']);
      if($harga){
        $ringkas = "{$actor} mengubah harga {$jenis} {$namaObjek} dari {$harga['o']} menjadi {$harga['n']}.";
      }
    }
    // nama
    if(!$ringkas){
      $nm = $cari(['nama','name']);
      if($nm){
        $ringkas = "{$actor} mengganti nama {$jenis} dari “{$nm['o']}” menjadi “{$nm['n']}”.";
      }
    }
    // transaksi posted/void (kalimat khusus)
    if(!$ringkas && baseType($auditLog->subject_type) === 'Transaksi'){
      $st = $cari(['status']);
      $tot= $cari(['total_harga']); $met= $cari(['metode_bayar']);
      if($st && $st['n'] === 'posted'){
        $ringkas = "{$actor} mengonfirmasi {$jenis} {$namaObjek} dengan total ".($tot['n'] ?? '—')." (metode ".($met['n'] ?? '—').").";
      } elseif($st && $st['n'] === 'void') {
        $ringkas = "{$actor} membatalkan {$jenis} {$namaObjek}.";
      }
    }
    // fallback umum
    if(!$ringkas){
      if(count($diffs)){
        $d = $diffs[0];
        $ringkas = "{$actor} mengubah {$jenis} {$namaObjek} — {$d['label']}: “{$d['o']}” → “{$d['n']}”.";
      } else {
        $ringkas = "{$actor} memperbarui {$jenis} {$namaObjek}.";
      }
    }
  }

  // apakah ada perubahan gambar?
  $hasMedia = count($media) > 0 || count($fallbackImageDiffs) > 0;
@endphp

<style>
  /* Gaya ringkas & rapi */
  #audit-simple .card{border:0;border-radius:16px}
  #audit-simple .card-header{
    background:linear-gradient(135deg,#f7f9fc,#eef2f7);
    border-bottom:1px solid #e9edf3;
    position:relative; padding:14px 16px 18px; margin-bottom:6px;
  }
  .section-title{font-weight:800;color:#334155;margin:12px 0 8px}
  .muted{color:#8a94a6}
  .bullet-list{margin:0;padding-left:1rem}
  .bullet-list li{margin:.25rem 0}
</style>

<div id="audit-simple" class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Detail Aktivitas</h5>
      <small class="text-muted">Ringkasan singkat untuk peninjauan</small>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
      <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline><line x1="9" y1="12" x2="21" y2="12"></line></svg>
      Kembali
    </a>
  </div>

  <div class="card-body">
    {{-- Ringkasan kalimat sederhana --}}
    <p class="mb-2">{{ $ringkas }}</p>
    <div class="muted mb-3">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</div>

    {{-- Daftar perubahan (tanpa jargon, before → after) --}}
    @if(count($diffs))
      <div class="section-title">Perubahan Data</div>
      <ul class="bullet-list">
        @foreach($diffs as $d)
          <li><strong>{{ $d['label'] }}</strong>: “{{ $d['o'] }}” → “{{ $d['n'] }}”</li>
        @endforeach
      </ul>
    @else
      <div class="muted">Tidak ada perubahan yang tercatat.</div>
    @endif

    {{-- Perubahan Gambar --}}
    @if($hasMedia)
      <hr>
      <div class="section-title">Perubahan File/Gambar</div>
      <div class="row g-3">
        {{-- Sumber dari media_changes --}}
        @foreach($media as $m)
          @php
            $attr   = $m['attribute'] ?? 'Gambar';
            $oldUrl = imageUrlOrNull($m['old'] ?? null);
            $newUrl = imageUrlOrNull($m['new'] ?? null);
          @endphp
          <div class="col-md-6">
            <div class="border rounded p-2 h-100">
              <div class="small text-muted mb-2"><strong>{{ $attr }}</strong></div>
              <div class="row">
                <div class="col-6">
                  <div class="small text-muted mb-1">Sebelumnya</div>
                  @if($oldUrl)
                    <img src="{{ $oldUrl }}" alt="sebelumnya" class="img-fluid rounded border">
                  @else
                    <div class="text-muted small fst-italic">— gambar sebelumnya tidak ditemukan —</div>
                  @endif
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">Sesudah</div>
                  @if($newUrl)
                    <img src="{{ $newUrl }}" alt="sesudah" class="img-fluid rounded border">
                  @else
                    <div class="text-muted small fst-italic">— tidak ada gambar —</div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach

        {{-- Fallback dari field image_* (mis. image_path) --}}
        @foreach($fallbackImageDiffs as $img)
          @php
            $oldUrl = imageUrlOrNull(is_string($img['_raw_o'] ?? null) ? $img['_raw_o'] : null);
            $newUrl = imageUrlOrNull(is_string($img['_raw_n'] ?? null) ? $img['_raw_n'] : null);
          @endphp
          @if($oldUrl || $newUrl)
            <div class="col-md-6">
              <div class="border rounded p-2 h-100">
                <div class="small text-muted mb-2"><strong>{{ $img['label'] }}</strong></div>
                <div class="row">
                  <div class="col-6">
                    <div class="small text-muted mb-1">Sebelumnya</div>
                    @if($oldUrl)
                      <img src="{{ $oldUrl }}" alt="sebelumnya" class="img-fluid rounded border">
                    @else
                      <div class="text-muted small fst-italic">— gambar sebelumnya tidak ditemukan —</div>
                    @endif
                  </div>
                  <div class="col-6">
                    <div class="small text-muted mb-1">Sesudah</div>
                    @if($newUrl)
                      <img src="{{ $newUrl }}" alt="sesudah" class="img-fluid rounded border">
                    @else
                      <div class="text-muted small fst-italic">— tidak ada gambar —</div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection
