@extends('admin.layouts.main')

@section('content')
@php
    $sourceLabels = config('home_sections.sources');
    $layoutLabels = config('home_sections.layouts');
    $filterLabels = config('home_sections.auto_filters');
@endphp
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Section Home</h4>
                    <p class="text-muted mb-0">Atur section dinamis yang tampil di home mobile — urutan, sumber data (produk/layanan/voucher/inspirasi/blog), layout, dan cara pemilihan datanya.</p>
                </div>
                <a href="javascript:void(0)" id="tambahHomeSection" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Section</a>
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
                                <th style="width:70px" class="text-center">Urutan</th>
                                <th>Section</th>
                                <th>Sumber</th>
                                <th>Layout</th>
                                <th>Data</th>
                                <th class="text-center">Maks</th>
                                <th>Status</th>
                                <th class="text-center" style="width:110px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sections as $i => $section)
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center" style="gap:2px">
                                            <a href="javascript:void(0)" onclick="reorderHomeSection({{ $section->id }}, 'up')" class="btn btn-light btn-xs @if($loop->first) disabled @endif" title="Naik"><i class="ri-arrow-up-s-line"></i></a>
                                            <a href="javascript:void(0)" onclick="reorderHomeSection({{ $section->id }}, 'down')" class="btn btn-light btn-xs @if($loop->last) disabled @endif" title="Turun"><i class="ri-arrow-down-s-line"></i></a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $section->title ?: '(tanpa judul)' }}</span>
                                        @if ($section->subtitle)<div><small class="text-muted">{{ $section->subtitle }}</small></div>@endif
                                    </td>
                                    <td><span class="badge badge-sm badge-info">{{ $sourceLabels[$section->source_type] ?? $section->source_type }}</span></td>
                                    <td><span class="text-muted">{{ \Illuminate\Support\Str::before($layoutLabels[$section->layout] ?? $section->layout, ' (') }}</span></td>
                                    <td>
                                        @if ($section->selection_mode === 'manual')
                                            <span class="badge badge-sm" style="background:#e8f6f7; color:#0e4751; border:1px solid #b9e2e6;">Manual · {{ $section->items_count }} item</span>
                                        @else
                                            <span class="badge badge-sm badge-primary">Otomatis · {{ $filterLabels[$section->source_type][$section->auto_filter] ?? $section->auto_filter }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $section->max_items }}</td>
                                    <td>
                                        @if ($section->is_active)
                                            <span class="badge badge-success badge-sm">Aktif</span>
                                        @else
                                            <span class="badge badge-danger badge-sm">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center" style="gap:8px">
                                            <a href="javascript:void(0)" data-bind-home-section="{{ $section->id }}" class="btn btn-success btn-xs editHomeSection" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" onclick="deleteHomeSection({{ $section->id }})" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada section. Klik <b>Tambah Section</b> untuk membuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHomeSectionEdit" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Edit Section</h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalHomeSectionCreate" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Tambah Section</h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        const modalCreate = $.modalCustom({ trigger: '#tambahHomeSection', modal: '#modalHomeSectionCreate', options: { title: 'Tambah Section', backdrop: 'static', keyboard: false, focus: false, show: false } });
        const modalEdit = $.modalCustom({ trigger: '.editHomeSection', modal: '#modalHomeSectionEdit', options: { title: 'Edit Section', bind: 'home-section', backdrop: 'static', keyboard: false, focus: false, show: false } });

        modalCreate.onShow(function() {
            $('#tambahHomeSection').spinner('show');
            $.get('{{ route("admin.mobile.home_sections.forms") }}', { view: 'home-section-create' })
                .done((r) => modalCreate.render(r))
                .fail((e) => modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                .always(() => $('#tambahHomeSection').spinner('hide'));
        });
        modalEdit.onShow(function(id) {
            $(`[data-bind-home-section=${id}]`).spinner();
            $.get('{{ route("admin.mobile.home_sections.forms") }}', { view: 'home-section-edit', id_home_section: id })
                .done((r) => modalEdit.render(r))
                .fail((e) => modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                .always(() => $(`[data-bind-home-section=${id}]`).spinner('hide'));
        });
    });

    function reorderHomeSection(id, direction) {
        $.post('{{ route("admin.mobile.home_sections.reorder") }}', { id_home_section: id, direction, _token: '{{ csrf_token() }}' })
            .done(() => location.reload())
            .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Gagal mengubah urutan.', position: 'top-right', icon: 'warning' }));
    }

    function deleteHomeSection(id) {
        Swal.fire({ title: 'Kamu yakin?', text: 'Section ini akan dihapus dari home.', icon: 'warning', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', showConfirmButton: true, showCancelButton: true, customClass: { cancelButton: 'bg-danger', confirmButton: 'bg-primary' } }).then((result) => {
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.home_sections.destroy") }}', { id_home_section: id, _token: '{{ csrf_token() }}' })
                .done((r) => { $.toast({ heading: 'Sukses', text: r.message, showHideTransition: 'plain', position: 'top-right', icon: 'success' }); setTimeout(() => location.reload(), 700); })
                .fail((e) => $.toast({ heading: 'Warning', text: (e.responseJSON||{}).message || 'Terjadi kesalahan.', position: 'top-right', icon: 'warning' }));
        });
    }
</script>
@endsection
