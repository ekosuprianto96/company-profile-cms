<div class="card" id="{{ $id }}">
    <div class="card-body">
        <h4 class="card-title">Meta Data</h4>
        <form method="POST" action="{{ route('admin.pages.store', ['id' => $config['id'], 'type' => 'meta']) }}" enctype="multipart/form-data" class="forms-sample">
            @csrf
            <div class="form-group">
                <label for="title">Meta Title</label>
                <input type="text" class="form-control" name="title" value="{{ $item['title'] }}" id="title" placeholder="Meta Title">
            </div>
            <div class="form-group">
                <label for="description">Meta Description</label>
                <textarea name="description" style="height: 150px;line-height: 20px" class="form-control" id="description" placeholder="Meta Description">{{ $item['description'] }}</textarea>
            </div>
            <div class="form-group">
                <label for="meta_descriptions" class="form-label">Keywords</label>
                <select name="keywords[]" id="keywords" class="form-control" multiple="multiple">
                    @foreach (explode(',', $item['keywords'] ?? '') as $keyword)
                        <option selected value="{{ $keyword }}">{{ $keyword }}</option>
                    @endforeach
                </select>
            </div>
            @if(isset($item['image']))
                <div class="form-group">
                    <label for="image" class="form-label">Meta Image</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.svg,.webp" class="form-control">
                </div>
            @endif
            <div class="form-group">
                <button class="btn btn-success pull-right">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#keywords').select2({
            tags: true,
            width: '100%'
        });
    })
</script>