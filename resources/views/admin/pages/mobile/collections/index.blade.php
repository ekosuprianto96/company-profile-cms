@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                <div>
                    <h4 class="card-title mb-1">Koleksi Data</h4>
                    <p class="text-muted mb-0">Buat kumpulan data (mis. Jenis Kebutuhan, Opsi Budget) beserta field-nya. Koleksi bisa dipakai sebagai <b>sumber data</b> di Form Builder — tanpa perlu bikin modul baru.</p>
                </div>
                <a href="javascript:void(0)" id="btnAddCollection" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Buat Koleksi</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="table-responsive"><table class="table align-middle w-100">
                <thead><tr>
                    <th>Nama Koleksi</th><th>Slug</th><th class="text-center">Field</th><th class="text-center">Data</th>
                    <th class="text-center">Status</th><th class="text-center" style="width:150px">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse ($collections as $c)
                        <tr>
                            <td><span class="fw-semibold">{{ $c->name }}</span>@if($c->description)<div class="text-muted" style="font-size:.8em">{{ \Illuminate\Support\Str::limit($c->description, 60) }}</div>@endif</td>
                            <td><code class="text-muted">collection:{{ $c->id }}</code></td>
                            <td class="text-center">{{ $c->fields_count }}</td>
                            <td class="text-center">{{ $c->entries_count }}</td>
                            <td class="text-center">@if($c->is_active)<span class="badge badge-success badge-sm">Aktif</span>@else<span class="badge badge-secondary badge-sm">Nonaktif</span>@endif</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center" style="gap:8px">
                                    <a href="{{ route('admin.mobile.collections.manage', $c->id) }}" class="btn btn-success btn-xs" title="Kelola"><i class="ri-settings-3-line"></i> Kelola</a>
                                    <a href="javascript:void(0)" onclick="deleteCollection({{ $c->id }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada koleksi. Klik <b>Buat Koleksi</b> untuk mulai.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>

    <div class="modal fade" id="modalAddCollection" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Buat Koleksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="form-group"><label class="form-label">Nama Koleksi</label>
                <input type="text" id="colName" class="form-control" placeholder="Contoh: Jenis Kebutuhan">
                <div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi <small class="text-muted">(opsional)</small></label>
                <textarea id="colDesc" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer"><button type="button" id="btnSaveCollection" class="btn btn-primary">Buat &amp; Kelola</button></div>
    </div></div></div>
</div>

<script>
    $('#btnAddCollection').on('click', () => new bootstrap.Modal(document.getElementById('modalAddCollection')).show());
    $('#btnSaveCollection').on('click', function () {
        const $btn = $(this).prop('disabled', true);
        $('[data-error="name"] span').text('');
        $.post('{{ route("admin.mobile.collections.store") }}', { name: $('#colName').val(), description: $('#colDesc').val(), _token: '{{ csrf_token() }}' })
            .done((r) => { window.location = '{{ url("admin/mobile/collections") }}/' + r.id; })
            .fail((e) => {
                const r = e.responseJSON || {};
                if (r.errors && r.errors.name) $('[data-error="name"] span').text(r.errors.name[0]);
                $.toast({ heading: 'Warning', text: r.message || 'Gagal.', position: 'top-right', icon: 'warning' });
                $btn.prop('disabled', false);
            });
    });
    function deleteCollection(id) {
        Swal.fire({ title: 'Hapus koleksi?', text: 'Semua field & data di dalamnya ikut terhapus.', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((res) => {
            if (!res.isConfirmed) return;
            $.post('{{ route("admin.mobile.collections.destroy") }}', { id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 600); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON || {}).message || 'Gagal.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
