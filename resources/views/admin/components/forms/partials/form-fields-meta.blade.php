@php $f = $form ?? null; @endphp

<form id="{{ $formId }}" class="forms-sample">
    @csrf
    <div class="form-group">
        <label class="form-label">Nama Form</label>
        <input name="name" type="text" class="form-control" value="{{ optional($f)->name }}" placeholder="mis. Form Pengajuan Umum">
        <div data-error="name"><span class="text-danger" style="font-size:.8em"></span></div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi <small class="text-muted">(opsional)</small></label>
        <textarea name="description" rows="2" class="form-control" placeholder="Keterangan singkat kegunaan form ini">{{ optional($f)->description }}</textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Tampilkan Nama Layanan di Header</label>
        <select name="show_service_header" class="form-control">
            <option value="1" @selected($f === null ? true : $f->show_service_header)>Ya — tampilkan nama layanan</option>
            <option value="0" @selected($f && ! $f->show_service_header)>Tidak — tampilkan nama form</option>
        </select>
        <small class="text-muted">Di aplikasi, header form menampilkan nama layanan yang dipilih user (bukan nama form ini).</small>
    </div>

    <div class="form-group">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-control">
            <option value="1" @selected(optional($f)->is_active ?? true)>Aktif</option>
            <option value="0" @selected($f && ! $f->is_active)>Nonaktif</option>
        </select>
    </div>
</form>
