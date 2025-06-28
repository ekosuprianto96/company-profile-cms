@extends('admin.layouts.main')

<style>
    .list_widgets {
        transition: transform 0.3s ease-in-out;
        transform: scale(1); /* nilai awal */
    }

    .list_widgets:hover {
        transform: scale(1.06)
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4>List Widgets</h4>
                    <hr>
                    <div class="row">
                        @if($widgets->count() > 0)
                            @foreach($widgets as $key => $value)
                                <div class="col-md-3 list_widgets">
                                    <a href="{{ route('admin.widgets.edit', $value->id) }}" style="text-decoration: none">
                                        <div class="card shadow">
                                            <div class="card-body">
                                                <div class="rounded mb-3 d-flex justify-content-center align-items-center" style="width: 100%;height: 200px;background-color: rgb(209, 209, 209)">
                                                    <i class="ri-stack-line" style="font-size: 4em"></i>
                                                </div>
                                                <div>
                                                    <h5 class="text-muted">{{ $value->title }}</h5>
                                                    <p class="text-truncated" style="color: rgb(194, 194, 194)">{{ $value->dscription }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div style="height: 300px;background-color: rgb(204, 204, 204)" class="text-center d-flex justify-content-center align-items-center rounded">
                                            <h6>Tidak Ada Widget</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
