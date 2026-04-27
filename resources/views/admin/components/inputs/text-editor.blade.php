<div class="form-group">
    <label for="{{ $id ?? '-' }}" class="form-label">{{ $label ?? '-' }} @if(isset($notice) && $notice['position'] == 'top') <span class="text-danger" style="font-size: 0.8em">{{ $notice['text'] }}</span> @endif</label>
    @if(isset($notice) && $notice['position'] == 'bottom') 
        <span class="text-danger d-block mb-2" style="font-size: 0.7em">{{ $notice['text'] }}</span> 
    @endif
    <textarea 
        class="form-control" 
        name="{{ $name }}" 
        id="{{ $id }}" 
        style="height: {{ $height ?? '100px' }};line-height: 20px" 
        placeholder="{{ $placeholder ?? '' }}"
    >{{ $value ?? '' }}</textarea>
</div>
<script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
<script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
<script>
    var editorInstance;
    ClassicEditor
    .create( document.querySelector( '#content'), {
        extraPlugins: [
            function(editor) {
                createCustomUploadAdapterPlugin({
                    url: '{{ route('admin.ckeditor.upload') }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })(editor);

                new ImageRemovePlugin(editor);
            }
        ],
        removePlugins: ['Markdown'], // Matikan plugin Markdown
    })
    .then( editor => {
        editorInstance = editor;

        editor.on('image:removed', (event, {imageRemoved}) => {
            fetch('{{ route('admin.ckeditor.cleanup') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    images: imageRemoved
                })
            });
        });
    });
</script>