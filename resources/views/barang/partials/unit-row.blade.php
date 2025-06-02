@php($row = $row ?? [])
<tr>
  <td>
    <select name="units[{{ $index }}][unit_id]" class="form-select">
      @foreach($allUnits as $u)
        <option value="{{ $u->id }}"
          @selected(($row['unit_id'] ?? $row->unit_id ?? null) == $u->id)>
          {{ $u->kode }}
        </option>
      @endforeach
    </select>
  </td>
  <td><input type="number" name="units[{{ $index }}][harga_beli]"
             value="{{ $row['harga_beli'] ?? $row->harga_beli ?? '' }}"
             class="form-control"></td>
  <td><input type="number" name="units[{{ $index }}][harga_jual]"
             value="{{ $row['harga_jual'] ?? $row->harga_jual ?? '' }}"
             class="form-control"></td>
  <td><input type="number" name="units[{{ $index }}][stok]"
             value="{{ $row['stok'] ?? $row->stok ?? 0 }}"
             class="form-control"></td>
  <td><button type="button" class="btn btn-sm btn-outline-danger"
             onclick="removeRow(this)">&times;</button></td>
</tr>
