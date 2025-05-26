@extends('admin.layouts.main')

@section('content')
    @include('admin.components.forms.paket-edit', ['package' => $package])
@endsection