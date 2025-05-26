<style>
    .list_feature li.item_feature:hover {
        cursor: grab;
    }
</style>
<form action="{{ route('admin.packages.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-5">Form Create Paket</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-lg-0">
                            <div class="form-group">
                                <label for="name">Nama Paket</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" id="name" placeholder="Nama Paket">
                                @error('name')
                                    <div class="invalid-feedback">
                                        <span class="text-danger">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 mb-lg-0">
                            <div class="form-group">
                                <label for="description">Deskripsi Singkat</label>
                                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" id="description" placeholder="Masukkan deskripsi singkat paket ini...">
                                @error('description')
                                    <div class="invalid-feedback">
                                        <span class="text-danger">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12 mb-3 mb-lg-0">
                            <div class="form-group">
                                <label for="price">Harga Paket</label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" id="price" placeholder="Harga Paket cth: 10000...">
                                @error('price')
                                    <div class="invalid-feedback">
                                        <span class="text-danger">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 mb-lg-0">
                            <div class="form-group">
                                <label for="show_price">Tampilkan Harga</label>
                                <select name="show_price" class="form-control" id="show_price">
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya</option>
                                </select>
                                @error('show_price')
                                    <div class="invalid-feedback">
                                        <span class="text-danger">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 mb-lg-0">
                            <div class="form-group">
                                <label for="is_recommended">Paket Rekomendasi</label>
                                <select name="is_recommended" class="form-control" id="is_recommended">
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya</option>
                                </select>
                                @error('is_recommended')
                                    <div class="invalid-feedback">
                                        <span class="text-danger">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <span class="font-weight-bold">List Feature</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end align-items-center" style="gap: 10px">
                                        <a href="javascript:void(0)" id="add__feature" class="btn btn-success btn-sm"><i class="ri-add-line"></i>Tambah Feature</a>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="col-md-12">
                            <div id="list_feature_wrapper" style="min-height: 100px">
                                <ul class="list_feature m-0 p-0" style="list-style: none">
                                    <li id="input_feature" style="display: none" class="border p-3 mb-2 text-center">
                                        <div class="row">
                                            <div class="col-md-11">
                                                <input type="text" placeholder="Maukkan feature paket" class="form-control">
                                            </div>
                                            <div class="col-md-1">
                                                <div class="d-flex align-items-center justify-content-end gap-3">
                                                    <a href="javascript:void(0)" id="save__feature" class="btn btn-success btn-sm"><i class="ri-add-line"></i></a>
                                                    <a href="javascript:void(0)" id="close__input_feature" class="btn btn-danger btn-sm"><i class="ri-close-line"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @error('features')
                                        <li id="feature_empty" class="border text-danger border-danger p-3 text-center">{{ $message }}</li>
                                    @else
                                        <li id="feature_empty" class="border p-3 text-center">Tidak ada data</li>
                                    @enderror
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end align-items-center" style="gap: 10px">
                                <a href="{{ route('admin.packages.index') }}" class="btn btn-danger">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        $(".list_feature").sortable({
            placeholder: "ui-state-highlight", // Efek placeholder saat dragging
            update: function (event, ui) {
                let orderedIDs = [];
                $(".list_feature li").each(function () {
                    orderedIDs.push($(this).attr("data-id"));
                });
            }
        });

        $('#add__feature').on('click', function() {
            showInputFeature(true);
        });

        $('#close__input_feature').on('click', function() {
            showInputFeature(false);
        });

        $('#save__feature').on('click', function() {
            let text = $('#input_feature input').val();
            const id = Date.now();
            if(text.length > 0) {
                $('.list_feature').append(createLlistFeature({text: text, id}));
                showInputFeature(false);
            }

            $('.delete_feature')
            .each(function() {
                const $this = $(this);
                $($this)
                .off('click')
                .on('click', function() {
                    $(this).closest('.item_feature').remove();

                    showInputFeature(false);
                });
            });
        });
    });

    function showInputFeature(status = false) {
        if(status) {
            $('#feature_empty').hide();
            $('#input_feature')
            .show()
            .find('input')
            .focus();
        }else {
            const existsItems = $('.item_feature').length;

            if(existsItems <= 0) {
                $('#feature_empty').show();
            }

            $('#input_feature')
            .hide()
            .find('input')
            .val('');
        }

        return status;
    }

    function createLlistFeature({text = '', id = ''} = {}) {
        return `<li class="p-3 border mb-2 item_feature" data-id="${id}">
            <div class="row">
                <div class="col-md-11">
                    <div class="d-flex align-items-center h-100">
                        <input type="hidden" name="features[]" value="${text}">
                        <i class="ri-draggable"></i> ${text}
                    </div>
                </div>    
                <div class="col-md-1 text-end">
                    <a href="javascript:void(0)" data-id="${id}" class="btn btn-danger delete_feature btn-sm"><i class="ri-close-line"></i></a>
                </div>    
            </div>
        </li>`
    }
</script>