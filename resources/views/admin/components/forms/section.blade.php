<div class="row">
    @php($section = $page->sections()->where('id', $id_section)->first())
    @if($section &&  !empty($section['collection']))
        <div class="col-md-12">
            <div class="w-100 d-flex justify-content-end align-items-center gap-3">
                <a href="{{ url($section['action']['link']) }}" class="btn btn-primary btn-sm"><i class="ri-add-line"></i> {{ $section['action']['label'] ?? 'Create' }}</a>
            </div>
        </div>
    @endif
    <div class="col-md-12">
        <form class="w-100" method="POST" action="{{ route("admin.sections.update", ["page" => $page->id, 'section' => $id_section]) }}" enctype="multipart/form-data">
            @csrf
            {!! $forms->renderForm() !!}
            <div class="w-100 d-flex justify-content-end align-items-center gap-3">
                <button type="button" data-bs-dismiss="modal" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line"></i> Batal</button>
                <button class="btn btn-success btn-sm"><i class="ri-save-line"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>