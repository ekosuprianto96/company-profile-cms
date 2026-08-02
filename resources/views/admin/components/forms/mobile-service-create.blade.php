<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="title">Nama Layanan</label>
                <input name="title" type="text" class="form-control" id="title" placeholder="Contoh: Renovasi Rumah Premium">
                <div data-error="title" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="summary">Ringkasan</label>
                <input name="summary" type="text" class="form-control" id="summary" placeholder="Deskripsi singkat untuk card/home">
                <div data-error="summary" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Deskripsi</label>
        <textarea class="form-control" name="description" id="description" rows="4" placeholder="Penjelasan layanan untuk kebutuhan mobile"></textarea>
        <div data-error="description" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
    </div>

    @include('admin.components.forms.partials.category-picker', [
        'categoryTree' => $categoryTree ?? collect(),
        'selectedCategoryId' => null,
    ])

    @include('admin.components.forms.partials.service-form-price')

    <div class="form-group">
        <label for="request_flow_type">Tipe Flow Pengajuan</label>
        <select name="request_flow_type" id="request_flow_type" class="form-control">
            <option value="standard">Standard</option>
            <option value="event_project">Event Project</option>
        </select>
        <div data-error="request_flow_type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
        <small class="text-muted">Pilih Event Project untuk layanan seperti Wedding Organizer.</small>
    </div>



    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="icon_type">Tipe Icon</label>
                <select name="icon_type" id="icon_type" class="form-control">
                    <option value="icon">Text Icon</option>
                    <option value="image">Image Icon</option>
                </select>
                <div data-error="icon_type" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-9" id="icon_text_wrapper">
            @include('admin.components.forms.partials.icon-picker', [
                'iconFieldName' => 'icon',
                'iconLabel' => 'Icon (MaterialIcons)',
                'selectedIcon' => null,
            ])
        </div>
    </div>

    <div id="icon_image_wrapper" style="display: none;">
        <div class="form-group">
            <x-admin.forms.image-upload :label="'Upload Icon Image'" :id_input="'input_mobile_service_icon_image'" />
            <input type="hidden" name="file_name_input_mobile_service_icon_image" id="file_name_input_mobile_service_icon_image">
        </div>
    </div>

    <div class="form-group">
        <x-admin.forms.image-upload :label="'Upload Cover Image (Opsional)'" :id_input="'input_mobile_service_cover_image'" />
        <input type="hidden" name="file_name_input_mobile_service_cover_image" id="file_name_input_mobile_service_cover_image">
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="card_color">Warna Card</label>
                <input name="card_color" type="color" class="form-control" id="card_color" value="#6ec7d0">
                <div data-error="card_color" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="text_color">Warna Teks</label>
                <input name="text_color" type="color" class="form-control" id="text_color" value="#0e4751">
                <div data-error="text_color" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input name="sort_order" type="number" min="0" class="form-control" id="sort_order" value="0">
                <div data-error="sort_order" class="invalid-fedback"><span class="text-danger" style="font-size: 0.8em"></span></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_new">Is New</label>
                <select name="is_new" id="is_new" class="form-control">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_featured">Tampil di Featured</label>
                <select name="is_featured" id="is_featured" class="form-control">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_popular">Tampil di Popular</label>
                <select name="is_popular" id="is_popular" class="form-control">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_active">Status</label>
                <select name="is_active" id="is_active" class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="is_coming_soon">Coming Soon</label>
                <select name="is_coming_soon" id="is_coming_soon" class="form-control">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                </select>
                <small class="text-muted">Jika "Ya", klik layanan di mobile memunculkan info "segera hadir".</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAddMobileService" class="btn btn-primary me-2">Submit</button>
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
                $('[name=file_name_input_mobile_service_icon_image]').val('');
            }
            resetValidation('icon_type');
        });

        $('input, select, textarea').on('keyup change', function() {
            const field = $(this).attr('name');
            if (field) resetValidation(field);
        });

        $('#buttonAddMobileService').click(function() {
            $.post('{{ route('admin.mobile.services.store') }}', {
                title: $('[name=title]').val(),
                category_id: $('[name=category_id]').val(),
                request_flow_type: $('[name=request_flow_type]').val(),
                summary: $('[name=summary]').val(),
                description: $('[name=description]').val(),
                icon_type: $('[name=icon_type]').val(),
                icon: $('[name=icon]').val(),
                icon_image: $('[name=file_name_input_mobile_service_icon_image]').val(),
                cover_image: $('[name=file_name_input_mobile_service_cover_image]').val(),
                card_color: $('[name=card_color]').val(),
                text_color: $('[name=text_color]').val(),
                sort_order: $('[name=sort_order]').val(),
                is_new: $('[name=is_new]').val(),
                is_featured: $('[name=is_featured]').val(),
                is_popular: $('[name=is_popular]').val(),
                is_active: $('[name=is_active]').val(),
                is_coming_soon: $('[name=is_coming_soon]').val(),
                form_id: $('[name=form_id]').val(),
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
                // Halaman sendiri → redirect ke daftar; (fallback modal bila masih dipakai).
                if (window.$mobileServicesTable) {
                    $('#modalMobileServiceCreate').modal('hide');
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
