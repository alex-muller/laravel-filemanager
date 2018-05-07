<div class="form-group amfm">
  <div class="amfm-link amfm-image" @if(isset($dataTypeContent->{$row->field})) style="background-image: url('/amfm/{{ $dataTypeContent->{$row->field} }}')" @endif>
    <div class="amfm-image-close"></div>
  </div>
  <input
      class="form-control"
      name="{{ $row->field }}"
      data-name="{{ $row->display_name }}"
      type="text"
      @if($row->required == 1) required @endif
      placeholder="{{ isset($options->placeholder)? old($row->field, $options->placeholder): $row->display_name }}"
      value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@else{{old($row->field)}}@endif">
</div>
