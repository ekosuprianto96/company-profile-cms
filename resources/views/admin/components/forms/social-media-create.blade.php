<form class="forms-sample" action="{{ route('admin.social_media.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-5">Form Create Social Media</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input name="name" required type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" id="name" placeholder="Nama Social Media...">
                        @error('name')
                            <div class="invalid-feedback">
                                <span class="text-danger">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="link">URL</label>
                        <input name="link" required type="url" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" id="link" placeholder="Masukkan link social media...">
                        @error('link')
                            <div class="invalid-feedback">
                                <span class="text-danger">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        {{-- <input name="icon" type="text" class="form-control" id="icon" placeholder="icon menu"> --}}
                        <select id="iconSelected" class="form-control" name="icon">
                            <option value="">-- Pilih Icon --</option>
                        @foreach(config('styles.icons') as $key => $value)
                            <option @selected(old('icon') == $value) value="{{ $value }}" data-icon="{{ $value }}">{{ $value }}</option>
                        @endforeach
                        </select>
                        @error('icon')
                            <div class="invalid-feedback">
                                <span class="text-danger">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="an">Status</label>
                        <select required id="an" class="form-control" name="an">
                            <option @selected(old('an') == 1) value="1">Aktif</option>
                            <option @selected(old('an') == 0) value="0">Tidak Aktif</option>
                        </select>
                        @error('an')
                            <div class="invalid-feedback">
                                <span class="text-danger">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="action_target">Action Target</label>
                        <select required id="action_target" class="form-control" name="action_target">
                            <option @selected(old('action_target') == '_blank') value="_blank">Blank</option>
                            <option @selected(old('action_target') == '_self') value="_self">Self</option>
                            <option @selected(old('action_target') == '_parent') value="_parent">Parent</option>
                            <option @selected(old('action_target') == '_top') value="_top">Top</option>
                        </select>
                        @error('action_target')
                            <div class="invalid-feedback">
                                <span class="text-danger">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end" style="gap: 10px">
                <a href="{{ route('admin.social_media.index') }}" class="btn btn-danger">Kembali</a>
                <button type="submit" class="btn btn-primary me-2">Submit</button>
            </div>
        </div>
    </div>
</form>

<script>

    $(document).ready(function() {
        $('#iconSelected').select2({
            templateResult: formatIcon,  // Tampilkan ikon di dropdown
            templateSelection: formatIcon, // Tampilkan ikon saat dipilih
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
    })
</script>