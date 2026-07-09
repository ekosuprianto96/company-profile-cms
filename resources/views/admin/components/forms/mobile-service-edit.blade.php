@php
    $iconImagePath = public_path('assets/images/mobile-services/' . ($service->icon_image ?? ''));
    $iconImageExists = !empty($service->icon_image) && file_exists($iconImagePath);

    $coverImagePath = public_path('assets/images/mobile-services/' . ($service->cover_image ?? ''));
    $coverImageExists = !empty($service->cover_image) && file_exists($coverImagePath);
@endphp

<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="title">Nama Layanan</label>
                <input name="title" type="text" class="form-control" id="title" value="{{ $service->title }}" placeholder="Contoh: Renovasi Rumah Premium">
                <div data-error="title" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="summary">Ringkasan</label>
                <input name="summary" type="text" class="form-control" id="summary" value="{{ $service->summary }}" placeholder="Deskripsi singkat untuk card/home">
                <div data-error="summary" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Deskripsi</label>
        <textarea class="form-control" name="description" id="description" rows="4" placeholder="Penjelasan layanan untuk kebutuhan mobile">{{ $service->description }}</textarea>
        <div data-error="description" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="form-group">
        <label for="request_flow_type">Tipe Flow Pengajuan</label>
        <select name="request_flow_type" id="request_flow_type" class="form-control">
            <option @selected(($service->request_flow_type ?? 'standard') === 'standard') value="standard">Standard</option>
            <option @selected(($service->request_flow_type ?? 'standard') === 'event_project') value="event_project">Event Project</option>
        </select>
        <div data-error="request_flow_type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
        <small class="text-muted">Pilih Event Project untuk layanan seperti Wedding Organizer.</small>
    </div>

    <div class="form-group">
        <label for="need_types">Jenis Kebutuhan Layanan</label>
        <select name="need_types[]" id="need_types" class="form-control" multiple="multiple">
            @foreach($needTypes as $needType)
                <option @selected(in_array((int)$needType->id, $selectedNeedTypeIds ?? [], true)) value="{{ $needType->id }}">{{ $needType->name }}</option>
            @endforeach
        </select>
        <div data-error="need_types" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="icon_type">Tipe Icon</label>
                <select name="icon_type" id="icon_type" class="form-control">
                    <option @selected($service->icon_type === 'icon') value="icon">Text Icon</option>
                    <option @selected($service->icon_type === 'image') value="image">Image Icon</option>
                </select>
                <div data-error="icon_type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-9" id="icon_text_wrapper" style="display: {{ $service->icon_type === 'icon' ? 'block' : 'none' }};">
            <div class="form-group">
                <label for="icon">Nama Icon (MaterialIcons)</label>
                <input name="icon" type="text" class="form-control" id="icon" value="{{ $service->icon }}" placeholder="Contoh: home-repair-service">
                <div data-error="icon" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div id="icon_image_wrapper" style="display: {{ $service->icon_type === 'image' ? 'block' : 'none' }};">
        <div class="form-group">
            <x-admin.forms.image-upload
                :edit="true"
                :image="$iconImageExists ? image_url('mobile-services', $service->icon_image) : null"
                :label="'Upload Icon Image'"
                :id_input="'input_mobile_service_icon_image_edit'" />
            <input type="hidden" name="file_name_input_mobile_service_icon_image_edit" id="file_name_input_mobile_service_icon_image_edit">
            <input type="hidden" name="path_file_input_mobile_service_icon_image_edit" id="path_file_input_mobile_service_icon_image_edit" value="{{ $iconImageExists ? 'assets/images/mobile-services/' . $service->icon_image : '' }}">
        </div>
    </div>

    <div class="form-group">
        <x-admin.forms.image-upload
            :edit="true"
            :image="$coverImageExists ? image_url('mobile-services', $service->cover_image) : null"
            :label="'Upload Cover Image (Opsional)'"
            :id_input="'input_mobile_service_cover_image_edit'" />
        <input type="hidden" name="file_name_input_mobile_service_cover_image_edit" id="file_name_input_mobile_service_cover_image_edit">
        <input type="hidden" name="path_file_input_mobile_service_cover_image_edit" id="path_file_input_mobile_service_cover_image_edit" value="{{ $coverImageExists ? 'assets/images/mobile-services/' . $service->cover_image : '' }}">
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="card_color">Warna Card</label>
                <input name="card_color" type="color" class="form-control" id="card_color" value="{{ $service->card_color }}">
                <div data-error="card_color" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="text_color">Warna Teks</label>
                <input name="text_color" type="color" class="form-control" id="text_color" value="{{ $service->text_color }}">
                <div data-error="text_color" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="badge_text">Badge Text</label>
                <input name="badge_text" type="text" class="form-control" id="badge_text" value="{{ $service->badge_text }}" placeholder="Contoh: Best Seller">
                <div data-error="badge_text" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="{{ $service->sort_order }}">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="price_label">Price Label</label>
                <input name="price_label" type="text" class="form-control" id="price_label" value="{{ $service->price_label }}" placeholder="Contoh: Mulai Rp15jt">
                <div data-error="price_label" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="rating">Rating</label>
                <input name="rating" type="number" min="0" max="5" step="0.1" class="form-control" id="rating" value="{{ $service->rating }}" placeholder="4.9">
                <div data-error="rating" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="projects_label">Projects Label</label>
                <input name="projects_label" type="text" class="form-control" id="projects_label" value="{{ $service->projects_label }}" placeholder="Contoh: 320 proyek">
                <div data-error="projects_label" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="estimated_duration">Estimasi Durasi</label>
                <input name="estimated_duration" type="text" class="form-control" id="estimated_duration" value="{{ $service->estimated_duration }}" placeholder="Contoh: 30 - 45 hari">
                <div data-error="estimated_duration" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cta_text">CTA Text</label>
                <input name="cta_text" type="text" class="form-control" id="cta_text" value="{{ $service->cta_text }}" placeholder="Contoh: Ajukan Sekarang">
                <div data-error="cta_text" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_new">Is New</label>
                <select name="is_new" id="is_new" class="form-control">
                    <option @selected(!$service->is_new) value="0">Tidak</option>
                    <option @selected($service->is_new) value="1">Ya</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_featured">Tampil di Featured</label>
                <select name="is_featured" id="is_featured" class="form-control">
                    <option @selected($service->is_featured) value="1">Ya</option>
                    <option @selected(!$service->is_featured) value="0">Tidak</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_popular">Tampil di Popular</label>
                <select name="is_popular" id="is_popular" class="form-control">
                    <option @selected($service->is_popular) value="1">Ya</option>
                    <option @selected(!$service->is_popular) value="0">Tidak</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option @selected($service->is_active) value="1">Aktif</option>
                    <option @selected(!$service->is_active) value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdateMobileService" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        function resetValidation(name) {
            const normalizedName = (name || '').replace('[]', '');
            $(`[name="${name}"]`).removeClass('is-invalid');
            $(`[name="${normalizedName}"]`).removeClass('is-invalid');
            $(`[data-error=${normalizedName}]`).find('span').text('');
        }

        $('#icon_type').change(function() {
            const value = $(this).val();
            if (value === 'image') {
                $('#icon_text_wrapper').hide();
                $('#icon_image_wrapper').show();
            } else {
                $('#icon_text_wrapper').show();
                $('#icon_image_wrapper').hide();
                $('[name=file_name_input_mobile_service_icon_image_edit]').val('');
            }
            resetValidation('icon_type');
        });

        $('input, select, textarea').on('keyup change', function() {
            const field = $(this).attr('name');
            if (field) resetValidation(field);
        });

        $('#need_types').select2({
            width: '100%',
            placeholder: 'Pilih jenis kebutuhan layanan',
            dropdownParent: $('#modalMobileServiceEdit')
        });

        $('#buttonUpdateMobileService').click(function() {
            $.post('{{ route('admin.mobile.services.update', $service->id) }}', {
                title: $('[name=title]').val(),
                request_flow_type: $('[name=request_flow_type]').val(),
                summary: $('[name=summary]').val(),
                description: $('[name=description]').val(),
                icon_type: $('[name=icon_type]').val(),
                icon: $('[name=icon]').val(),
                icon_image: $('[name=file_name_input_mobile_service_icon_image_edit]').val(),
                icon_image_path: $('[name=path_file_input_mobile_service_icon_image_edit]').val(),
                cover_image: $('[name=file_name_input_mobile_service_cover_image_edit]').val(),
                cover_image_path: $('[name=path_file_input_mobile_service_cover_image_edit]').val(),
                card_color: $('[name=card_color]').val(),
                text_color: $('[name=text_color]').val(),
                badge_text: $('[name=badge_text]').val(),
                price_label: $('[name=price_label]').val(),
                rating: $('[name=rating]').val(),
                projects_label: $('[name=projects_label]').val(),
                estimated_duration: $('[name=estimated_duration]').val(),
                cta_text: $('[name=cta_text]').val(),
                sort_order: $('[name=sort_order]').val(),
                need_types: $('[name="need_types[]"]').val(),
                is_new: $('[name=is_new]').val(),
                is_featured: $('[name=is_featured]').val(),
                is_popular: $('[name=is_popular]').val(),
                is_active: $('[name=is_active]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $('#modalMobileServiceEdit').modal('hide');
                window.$mobileServicesTable.ajax.reload();
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
            })
            .fail(function(error) {
                const response = error.responseJSON || {};
                if (response.errors) {
                    $.parseErros(response.errors);
                }

                $.toast({
                    heading: 'Warning',
                    text: response.message || 'Terjadi kesalahan.',
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'warning'
                });
            });
        });
    });
</script>
