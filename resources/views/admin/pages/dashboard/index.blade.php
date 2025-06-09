@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="w-100 h-100 p-3 d-flex justify-content-start align-items-center">
                                    <i class="ri-file-text-line" style="font-size: 35px"></i>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="d-block mb-3" style="font-size: 15px">Total Post</span>
                                <span class="d-block" style="font-size: 25px;font-weight: bold">{{ $countPost }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="w-100 h-100 p-3 d-flex justify-content-start align-items-center">
                                    <i class="ri-group-line" style="font-size: 35px"></i>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="d-block mb-3" style="font-size: 15px">Total User</span>
                                <span class="d-block" style="font-size: 25px;font-weight: bold">{{ $userCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="w-100 h-100 p-3 d-flex justify-content-start align-items-center">
                                    <i class="ri-group-line" style="font-size: 35px"></i>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="d-block mb-3" style="font-size: 15px">Total Pengunjung</span>
                                <span class="d-block" style="font-size: 25px;font-weight: bold">300</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mt-4 d-flex flex-column">
        <div class="row flex-grow">
            <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
                <div class="card card-rounded">
                    <div class="card-body">
                        {!! $chart->container() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4 mt-4 grid-margin stretch-card">
        <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title card-title-dash">Pesan Belum Dibaca</h4>
                    </div>
                </div>
                <div class="mt-3">
                    @if($unreadMessages->count() > 0)
                        @foreach($unreadMessages as $key => $value)
                            <a href="{{ route('admin.email.show', $value->id) }}" style="text-decoration: none" class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex">
                                    <div class="wrapper">
                                        <p class="ms-1 mb-1 fw-bold text-success">
                                            <i class="ri-mail-unread-line me-2"></i> {{ $value->name }}
                                        </p>
                                        <small class="text-muted mb-0">{{ $value->email }}</small>
                                    </div>
                                </div>
                                <div class="text-muted text-small"> {{ $value->created_at->diffForHumans() }} </div>
                            </a>
                        @endforeach
                    @else 
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex w-100 justify-content-center align-items-center">
                                <div class="w-100">
                                    <p class="ms-1 mb-1 fw-bold text-center">
                                        Tidak Ada Pesan
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script src="{{ $chart->cdn() }}"></script>
{{ $chart->script() }}
<script>
    $('#meta_keywords').select2({
        tags: true,
        width: '100%'
    });

    $(function() {
        'use strict';
    })
</script>
@endsection