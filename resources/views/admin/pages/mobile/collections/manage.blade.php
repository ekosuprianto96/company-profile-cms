@extends('admin.layouts.main')

@section('content')
@php $fieldsJson = $collection->fields->map(fn($f) => ['id'=>$f->id,'key'=>$f->key,'label'=>$f->label,'type'=>$f->type,'options'=>$f->options,'is_required'=>$f->is_required]); @endphp

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm"><div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
            <div>
                <h4 class="card-title mb-1">{{ $collection->name }}</h4>
                <p class="text-muted mb-0">Kelola field (struktur) &amp; data koleksi. Dipakai di Form Builder via <code>collection:{{ $collection->id }}</code>.</p>
            </div>
            <a href="{{ route('admin.mobile.collections') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Daftar Koleksi</a>
        </div></div>
    </div>

    {{-- Info koleksi --}}
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <h6 class="mb-3">Info Koleksi</h6>
            <div class="form-group"><label class="form-label">Nama</label><input type="text" id="cName" class="form-control" value="{{ $collection->name }}"></div>
            <div class="form-group"><label class="form-label">Deskripsi</label><textarea id="cDesc" class="form-control" rows="2">{{ $collection->description }}</textarea></div>
            <div class="form-group"><label class="form-label">Field Label <small class="text-muted">(ditampilkan saat jadi source)</small></label>
                <select id="cLabelField" class="form-control">
                    <option value="">— otomatis (field pertama) —</option>
                    @foreach ($collection->fields as $f)
                        <option value="{{ $f->key }}" @selected($collection->label_field === $f->key)>{{ $f->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Status</label>
                <select id="cActive" class="form-control"><option value="1" @selected($collection->is_active)>Aktif</option><option value="0" @selected(!$collection->is_active)>Nonaktif</option></select>
            </div>
            <button type="button" id="btnSaveInfo" class="btn btn-primary btn-sm w-100"><i class="ri-save-3-line me-1"></i> Simpan</button>
        </div></div>
    </div>

    {{-- Field (skema) --}}
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Field (Struktur Data)</h6>
                <button type="button" class="btn btn-primary btn-xs" onclick="openFieldModal()"><i class="ri-add-line"></i> Tambah Field</button></div>
            <div class="table-responsive"><table class="table table-sm align-middle">
                <thead><tr><th>Label</th><th>Key</th><th>Tipe</th><th>Wajib</th><th class="text-center" style="width:90px">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($collection->fields as $f)
                        <tr>
                            <td class="fw-semibold">{{ $f->label }}</td>
                            <td><code class="text-muted">{{ $f->key }}</code></td>
                            <td><span class="badge badge-info badge-sm">{{ \App\Http\Controllers\Admin\Mobile\CollectionController::FIELD_TYPES[$f->type] ?? $f->type }}</span></td>
                            <td>@if($f->is_required)<span class="badge badge-danger badge-sm">Wajib</span>@else<span class="text-muted">—</span>@endif</td>
                            <td class="text-center"><div class="d-flex justify-content-center" style="gap:6px">
                                <a href="javascript:void(0)" class="btn btn-success btn-xs" onclick='openFieldModal(@json($f))'><i class="ri-pencil-line"></i></a>
                                <a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="deleteField({{ $f->id }})"><i class="ri-delete-bin-5-line"></i></a>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada field. Tambah field dulu sebelum mengisi data.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>

    {{-- Entry (data) --}}
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Data ({{ $collection->entries->count() }})</h6>
                <button type="button" class="btn btn-primary btn-xs" onclick="openEntryModal()" @disabled($collection->fields->isEmpty())><i class="ri-add-line"></i> Tambah Data</button></div>
            <div class="table-responsive"><table class="table table-sm align-middle">
                <thead><tr>@foreach ($collection->fields as $f)<th>{{ $f->label }}</th>@endforeach<th>Status</th><th class="text-center" style="width:90px">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($collection->entries as $e)
                        <tr>
                            @foreach ($collection->fields as $f)
                                <td>@php $v = $e->data[$f->key] ?? null; @endphp
                                    @if($f->type==='boolean'){{ $v ? 'Ya' : 'Tidak' }}@else{{ \Illuminate\Support\Str::limit((string)($v ?? '-'), 40) }}@endif</td>
                            @endforeach
                            <td>@if($e->is_active)<span class="badge badge-success badge-sm">Aktif</span>@else<span class="badge badge-secondary badge-sm">Off</span>@endif</td>
                            <td class="text-center"><div class="d-flex justify-content-center" style="gap:6px">
                                <a href="javascript:void(0)" class="btn btn-success btn-xs" onclick='openEntryModal(@json($e))'><i class="ri-pencil-line"></i></a>
                                <a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="deleteEntry({{ $e->id }})"><i class="ri-delete-bin-5-line"></i></a>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $collection->fields->count() + 2 }}" class="text-center text-muted py-3">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>

    {{-- Modal Field --}}
    <div class="modal fade" id="modalField" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="fieldModalTitle">Tambah Field</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="fId">
            <div class="form-group"><label class="form-label">Label</label><input type="text" id="fLabel" class="form-control" placeholder="Contoh: Nama Kebutuhan"><div data-error="label"><span class="text-danger" style="font-size:.8em"></span></div></div>
            <div class="form-group"><label class="form-label">Key <small class="text-muted">(huruf kecil/underscore)</small></label><input type="text" id="fKey" class="form-control" placeholder="nama_kebutuhan"><div data-error="key"><span class="text-danger" style="font-size:.8em"></span></div></div>
            <div class="form-group"><label class="form-label">Tipe</label><select id="fType" class="form-control">@foreach ($fieldTypes as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach</select></div>
            <div class="form-group d-none" id="fOptionsWrap"><label class="form-label">Pilihan <small class="text-muted">(satu per baris)</small></label><textarea id="fOptions" class="form-control" rows="3"></textarea></div>
            <label class="d-inline-flex align-items-center mt-1" style="gap:8px;cursor:pointer;"><input type="checkbox" id="fRequired" style="width:16px;height:16px;"> <span>Wajib diisi</span></label>
        </div>
        <div class="modal-footer"><button type="button" id="btnSaveField" class="btn btn-primary">Simpan Field</button></div>
    </div></div></div>

    {{-- Modal Entry --}}
    <div class="modal fade" id="modalEntry" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="entryModalTitle">Tambah Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><input type="hidden" id="eId"><div id="entryFormBody"></div></div>
        <div class="modal-footer"><button type="button" id="btnSaveEntry" class="btn btn-primary">Simpan Data</button></div>
    </div></div></div>
</div>

<script>
    const COLLECTION_ID = {{ $collection->id }};
    const FIELDS = @json($fieldsJson);
    const FT = @json($fieldTypes);
    const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    const CSRF = '{{ csrf_token() }}';

    // ---- Info ----
    $('#btnSaveInfo').on('click', function () {
        const $b = $(this).prop('disabled', true);
        $.post('{{ route("admin.mobile.collections.update", $collection->id) }}', { name:$('#cName').val(), description:$('#cDesc').val(), label_field:$('#cLabelField').val(), is_active:$('#cActive').val(), _token:CSRF })
            .done((r) => { $.toast({ heading:'Sukses', text:r.message, position:'top-right', icon:'success' }); setTimeout(()=>location.reload(),600); })
            .fail((e) => { $.toast({ heading:'Warning', text:(e.responseJSON||{}).message||'Gagal.', position:'top-right', icon:'warning' }); $b.prop('disabled', false); });
    });

    // ---- Field ----
    function slugKey(s){ return String(s||'').toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'').replace(/^([0-9])/,'f_$1'); }
    $('#fLabel').on('input', function(){ if(!$('#fId').val() && !$('#fKey').data('touched')) $('#fKey').val(slugKey(this.value)); });
    $('#fKey').on('input', function(){ $(this).data('touched', true); });
    $('#fType').on('change', function(){ $('#fOptionsWrap').toggleClass('d-none', this.value !== 'select'); });

    function openFieldModal(f){
        $('[data-error] span').text('');
        $('#fId').val(f?.id||''); $('#fLabel').val(f?.label||''); $('#fKey').val(f?.key||'').data('touched', !!f);
        $('#fType').val(f?.type||'text').trigger('change');
        $('#fOptions').val((f?.options||[]).join('\n'));
        $('#fRequired').prop('checked', !!f?.is_required);
        $('#fieldModalTitle').text(f ? 'Edit Field' : 'Tambah Field');
        new bootstrap.Modal(document.getElementById('modalField')).show();
    }
    $('#btnSaveField').on('click', function(){
        const $b=$(this).prop('disabled',true); $('[data-error] span').text('');
        const id=$('#fId').val();
        const url = id ? '{{ url("admin/mobile/collections/fields/update") }}/'+id : '{{ route("admin.mobile.collections.fields.store") }}';
        $.post(url, { collection_id:COLLECTION_ID, label:$('#fLabel').val(), key:$('#fKey').val(), type:$('#fType').val(), options_text:$('#fOptions').val(), is_required:$('#fRequired').is(':checked')?1:0, _token:CSRF })
            .done((r)=>{ $.toast({heading:'Sukses',text:r.message,position:'top-right',icon:'success'}); setTimeout(()=>location.reload(),500); })
            .fail((e)=>{ const r=e.responseJSON||{}; if(r.errors){ Object.keys(r.errors).forEach(k=>$(`[data-error="${k}"] span`).text(r.errors[k][0])); } $.toast({heading:'Warning',text:r.message||'Gagal.',position:'top-right',icon:'warning'}); $b.prop('disabled',false); });
    });
    function deleteField(id){
        Swal.fire({title:'Hapus field?',icon:'warning',showCancelButton:true,confirmButtonText:'Ya',cancelButtonText:'Batal',customClass:{cancelButton:'bg-danger',confirmButton:'bg-primary'}}).then((res)=>{
            if(!res.isConfirmed) return;
            $.post('{{ route("admin.mobile.collections.fields.destroy") }}', {id,_token:CSRF}).done((r)=>{ $.toast({heading:'Sukses',text:r.message,position:'top-right',icon:'success'}); setTimeout(()=>location.reload(),500); });
        });
    }

    // ---- Entry (form dinamis dari FIELDS) ----
    function entryInput(f, val){
        const name = `data[${esc(f.key)}]`;
        const req = f.is_required ? ' <span class="text-danger">*</span>' : '';
        let ctrl = '';
        if (f.type === 'textarea') ctrl = `<textarea name="${name}" class="form-control" rows="2">${esc(val??'')}</textarea>`;
        else if (f.type === 'number') ctrl = `<input type="number" name="${name}" class="form-control" value="${esc(val??'')}">`;
        else if (f.type === 'boolean') ctrl = `<select name="${name}" class="form-control"><option value="0">Tidak</option><option value="1" ${val?'selected':''}>Ya</option></select>`;
        else if (f.type === 'select') ctrl = `<select name="${name}" class="form-control"><option value="">— pilih —</option>${(f.options||[]).map(o=>`<option ${String(val)===String(o)?'selected':''}>${esc(o)}</option>`).join('')}</select>`;
        else ctrl = `<input type="text" name="${name}" class="form-control" value="${esc(val??'')}">`;
        return `<div class="form-group"><label class="form-label">${esc(f.label)}${req}</label>${ctrl}<div data-error="data.${esc(f.key)}"><span class="text-danger" style="font-size:.8em"></span></div></div>`;
    }
    function openEntryModal(e){
        const data = e?.data || {};
        $('#eId').val(e?.id||'');
        $('#entryFormBody').html(FIELDS.map(f => entryInput(f, data[f.key])).join('') +
            `<label class="d-inline-flex align-items-center mt-2" style="gap:8px;cursor:pointer;"><input type="checkbox" id="eActive" style="width:16px;height:16px;" ${(!e||e.is_active)?'checked':''}> <span>Aktif</span></label>`);
        $('#entryModalTitle').text(e ? 'Edit Data' : 'Tambah Data');
        new bootstrap.Modal(document.getElementById('modalEntry')).show();
    }
    $('#btnSaveEntry').on('click', function(){
        const $b=$(this).prop('disabled',true); $('#entryFormBody [data-error] span').text('');
        const data={}; $('#entryFormBody [name^="data["]').each(function(){ const k=$(this).attr('name').replace(/^data\[|\]$/g,''); data[k]=$(this).val(); });
        const id=$('#eId').val();
        const url = id ? '{{ url("admin/mobile/collections/entries/update") }}/'+id : '{{ route("admin.mobile.collections.entries.store") }}';
        $.post(url, { collection_id:COLLECTION_ID, data, is_active:$('#eActive').is(':checked')?1:0, _token:CSRF })
            .done((r)=>{ $.toast({heading:'Sukses',text:r.message,position:'top-right',icon:'success'}); setTimeout(()=>location.reload(),500); })
            .fail((e)=>{ const r=e.responseJSON||{}; if(r.errors){ Object.keys(r.errors).forEach(k=>$(`[data-error="${k}"] span`).text(r.errors[k][0])); } $.toast({heading:'Warning',text:r.message||'Gagal.',position:'top-right',icon:'warning'}); $b.prop('disabled',false); });
    });
    function deleteEntry(id){
        Swal.fire({title:'Hapus data?',icon:'warning',showCancelButton:true,confirmButtonText:'Ya',cancelButtonText:'Batal',customClass:{cancelButton:'bg-danger',confirmButton:'bg-primary'}}).then((res)=>{
            if(!res.isConfirmed) return;
            $.post('{{ route("admin.mobile.collections.entries.destroy") }}', {id,_token:CSRF}).done((r)=>{ $.toast({heading:'Sukses',text:r.message,position:'top-right',icon:'success'}); setTimeout(()=>location.reload(),500); });
        });
    }
</script>
@endsection
