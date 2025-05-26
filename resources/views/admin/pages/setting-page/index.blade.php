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
    <div class="row">
        <x-admin.templates.tab-component
            :tabs="$tabs"
            :targets="$targets"
        >
            @foreach($tabs as $key => $value)
                <x-slot :name="$value['id']">
                    {!! $value['view'] !!}
                </x-slot>
            @endforeach
        </x-admin.templates.tab-component>
    </div>
@endsection
