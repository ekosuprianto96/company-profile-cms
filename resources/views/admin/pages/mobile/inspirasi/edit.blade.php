@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @include('admin.pages.mobile.inspirasi._form', [
                    'action' => route('admin.mobile.inspirasi.update', $inspire->slug ?? '-'),
                    'inspire' => $inspire,
                ])
            </div>
        </div>
    </div>
</div>
@endsection
