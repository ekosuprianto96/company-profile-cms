@extends('admin.layouts.main')

@section('content')
    @include('admin.components.forms.informasi-edit-pengaturan-tema', ['themes' => $themes, 'id' => $id])
@endsection
