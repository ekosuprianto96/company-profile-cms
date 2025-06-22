<div class="form-group">
    <label for="{{ $id ?? '-' }}" class="form-label">{{ $label ?? '-' }} @if(isset($notice) && $notice['position'] == 'top') <span class="text-danger" style="font-size: 0.8em">{{ $notice['text'] }}</span> @endif</label>
    @if(isset($notice) && $notice['position'] == 'bottom') 
        <span class="text-danger d-block mb-2" style="font-size: 0.7em">{!! $notice['text'] !!}</span> 
    @endif
    <textarea 
        class="form-control" 
        name="{{ $name }}" 
        id="{{ $id }}" 
        style="height: {{ $height ?? '100px' }};line-height: 20px" 
        placeholder="{{ $placeholder ?? '' }}"
    >{!! $value ?? '' !!}</textarea>
</div>