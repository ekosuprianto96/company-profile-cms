
<style>
    .CodeMirror {
        border: 1px solid #ccc;
        height: 400px; /* Atur tinggi editor */
        font-family: monospace;
        padding: 10px;
    }

    .CodeMirror {
        box-sizing: content-box !important;
    }

</style>

<div class="card" id="{{ $id }}">
    <div class="card-body">
        <h4 class="card-title">Custom Scripts</h4>
        <div class="container-fluid p-0">
            <form method="POST" action="{{ route('admin.pages.store', ['id' => $config['id'], 'type' => 'scripts']) }}" class="m-0 p-0">
                @csrf
                <div class="form-group">
                    <textarea name="scripts" id="scriptsEditor">{!! implode("\n", $item ?? []) !!}</textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-success pull-right">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    CodeMirror.fromTextArea(document.getElementById("scriptsEditor"), {
        mode: "htmlmixed", // Sesuaikan dengan jenis kode yang diedit
        theme: "dracula", // Bisa diganti ke theme lain seperti "default"
        lineNumbers: true, // Menampilkan nomor baris
        autoCloseTags: true
    });
</script>