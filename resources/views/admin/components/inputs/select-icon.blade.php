<div class="form-group">
    <label for="{{ $id }}">{{ $label }} @if(isset($notice) && $notice['position'] == 'top') <span class="text-danger" style="font-size: 0.8em">{{ $notice['text'] }}</span> @endif</label>
    @if(isset($notice) && $notice['position'] == 'bottom') 
        <span class="text-danger d-block mb-2" style="font-size: 0.7em">{{ $notice['text'] }}</span> 
    @endif
    <select id="{{ $id }}" class="form-control" style="width: 100%" name="{{ $name }}">
      @foreach(config('styles.icons') as $key => $item)
          <option @selected($value == $item) value="{{ $item }}" data-icon="{{ $item }}">{{ $item }}</option>
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
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih,
            dropdownParent: $('#{{ $id }}').closest('.modal')
        });

        function formatIcon(option) {
            if (!option.id) {
                return option.text;
            }

            var $icon = $(
                '<span><i class="' + $(option.element).data('icon') + ' me-2"></i> <span>' + option.text + '</span></span>'
            );
            return $icon;
        }
    })(jQuery)
</script>