<form class="forms-sample" id="formEditFooter" action="" method="POST">
    @csrf
    <div class="form-group">
      <label for="tagline">Tagline</label>
      <input name="tagline" value="{{ $informasi->value->tagline }}" type="text" class="form-control" id="tagline" placeholder="Masukkan tagline aplikasi...">
      <div data-error="tagline" class="invalid-fedback">
          <span class="text-danger" style="font-size: 0.8em"></span>
      </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="backgroundColor">Background</label>
                <input name="backgroundColor" value="{{ $informasi->value->backgroundColor }}" type="color" class="form-control" id="backgroundColor">
                <div data-error="backgroundColor" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="textColor">Text Color</label>
                <input name="textColor" value="{{ $informasi->value->textColor }}" type="color" class="form-control" id="textColor">
                <div data-error="textColor" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <h4 class="card-title mb-5">Map : </h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="alamatMap">Alamat Di Map</label>
                <input name="map[alamat]" value="{{ $informasi->value->map->alamat }}" type="text" class="form-control" id="alamatMap">
                <div data-error="map[alamat]" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="mapHeight">Height</label>
                <input name="map[height]" value="{{ $informasi->value->map->height }}" type="text" class="form-control" id="mapHeight">
                <div data-error="map[height]" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <h4 class="card-title mb-5">Pengaturan Logo : </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="logo_width_footer">Width (satuan : px)</label>
                <input name="logo_width_footer" value="{{ $informasi->value->logo_width_footer }}" type="number" class="form-control" id="logo_width_footer">
                <div data-error="logo_width_footer" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdate" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>

    $(document).ready(function() {
        const inputForms = $('input, select');
        $.each(inputForms, function(index, value) {
            if(value.tagName === 'INPUT') {
                $(value).keyup(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }else if(value.tagName === 'SELECT') {
                $(value).change(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }
        });

        $('#buttonUpdate').click(function() {
            $.post('{{ route('admin.informasi.update', $informasi->id) }}', {
                ...createObjectFooter()
            }).done(function(response) {
                $('#modalUpdate').modal('hide');
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
            }).fail(function(error) {
                const response = error.responseJSON;

                if(response) {
                    const { errors } = response;
                    
                    if(errors) {
                        $.parseErros(errors);
                    }

                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                }
            })
        });
    });

    function createObjectFooter() {
        return {
            _token: '{{ csrf_token() }}',
            tagline: $('[name=tagline]').val(),
            backgroundColor: $('[name=backgroundColor]').val(),
            textColor: $('[name=textColor]').val(),
            logo_width_footer: $('[name=logo_width_footer]').val(),
            map: {
                alamat: $('[name="map[alamat]"]').val(),
                height: $('[name="map[height]"]').val()
            }
        }
    }
</script>