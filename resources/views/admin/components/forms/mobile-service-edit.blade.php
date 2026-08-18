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

    @include('admin.components.forms.partials.category-picker', [
        'categoryTree' => $categoryTree ?? collect(),
        'selectedCategoryId' => $service->category_id,
    ])

    @include('admin.components.forms.partials.service-form-price')

    <div class="form-group">
        <label for="request_flow_type">Tipe Flow Pengajuan</label>
        <select name="request_flow_type" id="request_flow_type" class="form-control">
            <option @selected(($service->request_flow_type ?? 'standard') === 'standard') value="standard">Standard</option>
            <option @selected(($service->request_flow_type ?? 'standard') === 'event_project') value="event_project">Event Project</option>
        </select>
        <div data-error="request_flow_type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
        <small class="text-muted">Pilih Event Project untuk layanan seperti Wedding Organizer.</small>
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
            @include('admin.components.forms.partials.icon-picker', [
                'iconFieldName' => 'icon',
                'iconLabel' => 'Icon (MaterialIcons)',
                'selectedIcon' => $service->icon,
            ])
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
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="{{ $service->sort_order }}">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
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
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_coming_soon">Coming Soon</label>
                <select name="is_coming_soon" id="is_coming_soon" class="form-control">
                    <option @selected(!$service->is_coming_soon) value="0">Tidak</option>
                    <option @selected($service->is_coming_soon) value="1">Ya</option>
                </select>
                <small class="text-muted">Jika "Ya", klik layanan di mobile memunculkan info "segera hadir".</small>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="card border-warning" style="background:#fffaf2;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start" style="gap:12px;">
                        <div>
                            <label class="fw-bold mb-0"><i class="ri-pause-circle-line me-1"></i> Stop Terima Pengajuan</label>
                            <div class="text-muted" style="font-size:12px;">Jika "Ya (stop)", tombol kirim di form pengajuan app otomatis dinonaktifkan &amp; catatan di bawah tampil sebagai peringatan ke user.</div>
                        </div>
                        <select name="submissions_paused" id="submissions_paused" class="form-control" style="max-width:150px;">
                            <option @selected(!$service->submissions_paused) value="0">Tidak</option>
                            <option @selected($service->submissions_paused) value="1">Ya (stop)</option>
                        </select>
                    </div>
                    <div id="pause_note_wrapper" class="mt-3" style="{{ $service->submissions_paused ? '' : 'display:none;' }}">
                        <label for="submissions_paused_note">Catatan alasan untuk user <span class="text-danger">*</span></label>
                        <select id="pause_preset" class="form-control mb-2">
                            <option value="">— Pilih catatan cepat (lalu boleh diedit) —</option>
                            @foreach(config('service_flags.pause_reason_presets', []) as $preset)
                                <option value="{{ $preset }}">{{ \Illuminate\Support\Str::limit($preset, 70) }}</option>
                            @endforeach
                        </select>
                        <textarea name="submissions_paused_note" id="submissions_paused_note" rows="3" class="form-control" placeholder="Tulis alasan yang tampil ke user…">{{ $service->submissions_paused_note }}</textarea>
                        <div data-error="submissions_paused_note" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
                    </div>
                </div>
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

        // Stop pengajuan: tampilkan/sembunyikan catatan + isi dari preset.
        $('#submissions_paused').change(function() {
            $('#pause_note_wrapper').toggle($(this).val() === '1');
        });
        $('#pause_preset').change(function() {
            const v = $(this).val();
            if (v) { $('#submissions_paused_note').val(v); resetValidation('submissions_paused_note'); }
        });

        $('#buttonUpdateMobileService').click(function() {
            $.post('{{ route('admin.mobile.services.update', $service->id) }}', {
                title: $('[name=title]').val(),
                category_id: $('[name=category_id]').val(),
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
                sort_order: $('[name=sort_order]').val(),
                is_new: $('[name=is_new]').val(),
                is_featured: $('[name=is_featured]').val(),
                is_popular: $('[name=is_popular]').val(),
                is_active: $('[name=is_active]').val(),
                is_coming_soon: $('[name=is_coming_soon]').val(),
                submissions_paused: $('[name=submissions_paused]').val(),
                submissions_paused_note: $('[name=submissions_paused_note]').val(),
                form_id: $('[name=form_id]').val(),
                step_template_id: $('[name=step_template_id]').val(),
                price_items: (window.collectServicePriceItems ? window.collectServicePriceItems() : []),
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
                if (window.$mobileServicesTable) {
                    $('#modalMobileServiceEdit').modal('hide');
                    window.$mobileServicesTable.ajax.reload();
                } else {
                    setTimeout(function() { window.location = '{{ route("admin.mobile.services") }}'; }, 600);
                }
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
