@extends('admin.layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="w-100 d-flex justify-content-end align-items-center gap-3">
                                <form method="POST" action="{{ route('admin.sitemap.generate') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-icon-text btn-primary"><i class="ri-loop-right-line"></i> Generate</button>
                                </form>
                                <a href="{{ url('sitemap') }}" target="_blank" class="btn btn-warning btn-icon-text btn-sm"><i class="ri-eye-line"></i> Preview</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="card-title card-title-dash">List URL</h4>
                                        <p class="mb-0"><strong>Terakhir Diperbarui : </strong>{{ $lastMod ?? '-' }}</p>
                                    </div>
                                    <ul class="bullet-line-list">
                                        @foreach(($info ?? []) as $key => $value)
                                            <li>
                                                <div class="d-flex justify-content-between">
                                                    <div><strong class="text-light-green">{{ $value['loc'] }}</strong></div>
                                                    <p>{{ \Carbon\carbon::parse($value['lastmod'] ?? date('M D Y'))->diffForHumans() }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
