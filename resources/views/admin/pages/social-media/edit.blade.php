@extends('admin.layouts.main')

@section('content')
    @include('admin.components.forms.social-media-edit', ['socialMedia' => $socialMedia])
@endsection