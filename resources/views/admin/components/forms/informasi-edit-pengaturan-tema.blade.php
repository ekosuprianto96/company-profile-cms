<div class="card">
    <div class="card-body">
        <form class="forms-sample" id="formEditTheme" action="{{ route('admin.themes.update', $id) }}" method="POST">
            @csrf
            <h4>Main Background</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="main_background[type]">Type</label>
                                <select name="main_background[type]" id="main_background[type]" class="form-control">
                                    <option @selected($themes['main_background']['type'] == 'solid') value="solid">Solid</option>
                                    <option @selected($themes['main_background']['type'] == 'gradient') value="gradient">Gradient</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="main_background[solid]">Solid Color</label>
                                <input name="main_background[solid]" type="color" value="{{ $themes['main_background']['solid'] }}" class="form-control" id="main_background[solid]">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="main_background[gradient][degre]">Rotate (deg)</label>
                                <input oninput="updatePreviewColor()" name="main_background[gradient][degre]" type="number" value="{{ $themes['main_background']['gradient']['degre'] }}" class="form-control" id="main_background_gradient_degre">
                            </div>
                        </div>
                        @foreach($themes['main_background']['gradient']['colors'] as $key => $value)
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="main_background[gradient][colors][{{ $key }}][color]">Color {{ $key + 1 }}</label>
                                            <input 
                                                oninput="updatePreviewColor()"
                                                name="main_background[gradient][colors][{{ $key }}][color]" 
                                                type="color" 
                                                value="{{ $value['color'] }}" 
                                                class="form-control gradient_colors" 
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="main_background[gradient][colors][{{ $key }}][color_stop]">Color Stop (%)</label>
                                            <input 
                                                oninput="updatePreviewColor()"
                                                name="main_background[gradient][colors][{{ $key }}][color_stop]" 
                                                type="number" 
                                                value="{{ $value['color_stop'] }}" 
                                                class="form-control gradient_color_stop" 
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-md-12">
                            <div 
                                id="preview_color" 
                                style="width: 100%;height: 50px;background-color: {{ $themes['main_background']['gradient']['colors'][0]['color'] }};background: {{ generateGradientColor($themes['main_background']['gradient']) }}"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
            <h4>Typography</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    @foreach($themes['typography']['size'] as $key => $value)
                        <div class="form-group">
                            <label for="typography_{{ $key }}">Text {{ ucfirst($key) }}</label>
                            <input name="typography[size][{{ $key }}]" type="text" value="{{ $value }}" class="form-control" id="typography_{{ $key }}">
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6">
                    @foreach($themes['typography']['colors'] as $key => $value)
                        <div class="form-group">
                            <label for="typography_color_{{ $key }}">Color {{ str_replace('_', ' ', $key) }}</label>
                            <input name="typography[colors][{{ $key }}]" type="color" value="{{ $value }}" class="form-control" id="typography_color_{{ $key }}">
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" id="buttonUpdate" class="btn btn-primary me-2">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>

    $(document).ready(function() {

        updatePreviewColor();
    });

    function updatePreviewColor() {
        const gradientColors = $('.gradient_colors');
        const gradientColorStops = $('.gradient_color_stop');
        const previewColor = $('#preview_color');

        const colorFormats = [];
        const colorStops = [];

        $.each(gradientColors, function(index, value) {
            colorFormats.push(`${$(value).val()} ${$(gradientColorStops[index]).val()}%`);
        });

        previewColor.css('background', `linear-gradient(${ $('#main_background_gradient_degre').val() }deg, ${colorFormats.join(', ')})`);
    }
</script>