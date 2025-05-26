<div class="form-group">
    <label for="{{ $id ?? '' }}">{{ $label ?? '-' }} @if(isset($notice) && $notice['position'] == 'top') <span class="text-danger" style="font-size: 0.8em">{{ $notice['text'] }}</span> @endif</label>
    @if(isset($notice) && $notice['position'] == 'bottom') 
        <span class="text-danger d-block mb-2" style="font-size: 0.7em">{{ $notice['text'] }}</span> 
    @endif
    <input name="{{ $name ?? '' }}" value="{{ $value ?? '' }}" type="text" class="form-control" id="{{ $id ?? '' }}" placeholder="{{ $placeholder ?? '' }}">
    <div data-error="{{ $name ?? '' }}" class="invalid-fedback">
        <span class="text-danger" style="font-size: 0.8em"></span>
    </div>
</div>