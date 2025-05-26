@php
    [$object, $method] = explode('@', $options);
    $options = call_user_func([app($object), $method]);
@endphp

<div class="form-group">
    <label for="{{ $id }}">{{ $label }} @if(isset($notice) && $notice['position'] == 'top') <span class="text-danger" style="font-size: 0.8em">{{ $notice['text'] }}</span> @endif</label>
    @if(isset($notice) && $notice['position'] == 'bottom') 
        <span class="text-danger d-block mb-2" style="font-size: 0.7em">{{ $notice['text'] }}</span> 
    @endif
    <select id="{{ $id }}" class="form-control" style="width: 100%" name="{{ $name }}">
      <option value="">{{ $placeholder }}</option>
      @foreach($options as $key => $item)
          <option @selected($value == $item) value="{{ $item }}">{{ $item }}</option>
      @endforeach
    </select>
    <div data-error="{{ $name }}" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
    </div>
</div>

<script>
    (function($) {
        'use strict'

        $('#{{ $id }}').select2({
            witdh: '100%',
            dropdownParent: $('#{{ $id }}').closest('.modal')
        });
    })(jQuery)
</script>