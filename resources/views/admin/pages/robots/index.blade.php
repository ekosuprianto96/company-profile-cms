@extends('admin.layouts.main')

@push('codemirror')
<!-- Tambahkan CodeMirror -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/edit/closetag.min.js"></script>
@endpush

@section('content')
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
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="container-fluid p-0">
                        <form action="{{ route("admin.robots.update") }}" method="POST" class="m-0 d-flex justify-content-center w-100 align-items-center flex-column p-0">
                            @csrf
                            <div class="w-50">
                                <div class="form-group">
                                    <div class="d-flex w-100 justify-content-end align-items-center">
                                        <button type="submit" class="btn btn-sm btn-icon-text btn-success">
                                            <i class="ri-save-line"></i> Update
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <textarea name="robots" id="codeRobots">{!! $robots !!}</textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        CodeMirror.fromTextArea(document.getElementById("codeRobots"), {
            mode: "htmlmixed", // Sesuaikan dengan jenis kode yang diedit
            theme: "dracula", // Bisa diganti ke theme lain seperti "default"
            lineNumbers: true, // Menampilkan nomor baris
            autoCloseTags: true
        });
    </script>
@endsection
