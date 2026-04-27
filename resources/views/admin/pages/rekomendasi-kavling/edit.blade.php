@extends('admin.layouts.main')

@section('content')
    @include('admin.components.forms.rekomendasi-kavling-edit', ['rekomendasi' => $rekomendasi])
@endsection