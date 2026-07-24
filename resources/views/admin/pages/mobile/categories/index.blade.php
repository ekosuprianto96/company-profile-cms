@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Kategori</h4>
                    <p class="text-muted mb-0">Master data kategori bertingkat (kategori → sub-kategori → dst). Dipakai bersama oleh layanan &amp; produk.</p>
                </div>
                <a href="javascript:void(0)" id="tambahCategory" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Kategori</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th style="width:55%">Kategori</th>
                                <th>Ikon</th>
                                <th>Status</th>
                                <th class="text-center" style="width:110px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $cat)
                                <tr>
                                    <td>
                                        <span style="display:inline-block; width: {{ $cat->depth * 22 }}px"></span>
                                        @if ($cat->depth > 0)
                                            <i class="ri-corner-down-right-line text-muted me-1"></i>
                                        @endif
                                        <span class="fw-semibold">{{ $cat->name }}</span>
                                        @if ($cat->children_count > 0)
                                            <span class="badge badge-sm ms-1" style="background:#e8f6f7; color:#0e4751; border:1px solid #b9e2e6; font-weight:600;">{{ $cat->children_count }} sub</span>
                                        @endif
                                    </td>
                                    <td><code class="text-muted">{{ $cat->icon }}</code></td>
                                    <td>
                                        @if ($cat->is_active)
                                            <span class="badge badge-success badge-sm">Aktif</span>
                                        @else
                                            <span class="badge badge-danger badge-sm">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center" style="gap:8px">
                                            <a href="javascript:void(0)" data-bind-category="{{ $cat->id }}" class="btn btn-success btn-xs editCategory" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" onclick="deleteCategory({{ $cat->id }}, {{ $cat->children_count }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori. Klik <b>Tambah Kategori</b> untuk membuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCategoryEdit" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Edit Kategori</h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalCategoryCreate" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Tambah Kategori</h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        const modalCreate = $.modalCustom({ trigger: '#tambahCategory', modal: '#modalCategoryCreate', options: { title: 'Tambah Kategori', backdrop: 'static', keyboard: false, focus: false, show: false } });
        const modalEdit = $.modalCustom({ trigger: '.editCategory', modal: '#modalCategoryEdit', options: { title: 'Edit Kategori', bind: 'category', backdrop: 'static', keyboard: false, focus: false, show: false } });

        modalCreate.onShow(function() {
            $('#tambahCategory').spinner('show');
            $.get('{{ route("admin.mobile.categories.forms") }}', { view: 'category-create' })
                .done((r) => modalCreate.render(r))
                .fail((e) => modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                .always(() => $('#tambahCategory').spinner('hide'));
        });
        modalEdit.onShow(function(id) {
            $(`[data-bind-category=${id}]`).spinner();
            $.get('{{ route("admin.mobile.categories.forms") }}', { view: 'category-edit', id_category: id })
                .done((r) => modalEdit.render(r))
                .fail((e) => modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                .always(() => $(`[data-bind-category=${id}]`).spinner('hide'));
        });
    });

    function deleteCategory(id, childrenCount) {
        if (childrenCount > 0) {
            Swal.fire({ title: 'Tidak bisa dihapus', text: 'Kategori ini masih memiliki sub-kategori. Kosongkan/pindahkan sub-kategorinya dulu.', icon: 'warning', confirmButtonText: 'Mengerti' });
            return;
        }
        Swal.fire({ title: 'Kamu yakin?', text: 'Kategori ini akan dihapus.', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.categories.destroy") }}', { id_category: id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, showHideTransition: 'plain', position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', showHideTransition: 'slide', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
