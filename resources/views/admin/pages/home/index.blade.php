@extends('admin.layouts.main')

@section('content')
    @php
        $tabs = [
            [
                'title' => 'Section',
                'id' => 'section_list'
            ],
            [
                'title' => 'Meta Data',
                'id' => 'meta_data'
            ]
        ];

        $targets = [
            'section_list',
            'meta_data'
        ];
    @endphp
    <div class="row">
        <x-admin.templates.tab-component
            :tabs="$tabs"
            :targets="$targets"
        >
            <x-slot name="section_list">
                <div id="section_list" class="card">
                    <div class="card-body">
                        <h4 class="card-title">Pengaturan Section</h4>
                        <ul>
                            <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                                <div class="d-flex flex-column w-50">
                                    <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> Hero Banner</span>
                                </div>
                                <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                                    <a href="{{ route('admin.banners.index') }}" title="Edit" class="btn btn-rounded btn-sm">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                </div>
                            </li>
                            <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                                <div class="d-flex flex-column w-50">
                                    <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> Layanan Kami</span>
                                </div>
                                <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                                    <button title="Edit" class="btn btn-rounded btn-sm">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                </div>
                            </li>
                            <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                                <div class="d-flex flex-column w-50">
                                    <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> Galeri</span>
                                </div>
                                <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                                    <button title="Edit" class="btn btn-rounded btn-sm">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                </div>
                            </li>
                            <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                                <div class="d-flex flex-column w-50">
                                    <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> Paket</span>
                                </div>
                                <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                                    <button title="Edit" class="btn btn-rounded btn-sm">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                </div>
                            </li>
                            <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                                <div class="d-flex flex-column w-50">
                                    <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> Blog</span>
                                </div>
                                <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                                    <button title="Edit" class="btn btn-rounded btn-sm">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </x-slot>
            <x-slot name="meta_data">
                <div id="meta_data" class="card">
                    <div class="card-body">
                      <h4 class="card-title">Setting Meta Data SEO</h4>
                      <form class="forms-sample">
                        <div class="form-group">
                          <label for="exampleInputUsername1">Site Title</label>
                          <input type="text" class="form-control" id="meta_title" placeholder="Site Title">
                        </div>
                        <div class="form-group">
                          <label for="exampleInputUsername1">Meta Title</label>
                          <input type="text" class="form-control" id="meta_title" placeholder="Meta title page">
                        </div>
                        <div class="form-group">
                            <label for="meta_descriptions" class="form-label">Meta Descriptions</label>
                            <textarea class="form-control" style="height: 150px" id="meta_descriptions" placeholder="Meta descriptions page"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="meta_descriptions" class="form-label">Meta Keyword</label>
                            <select name="meta_keywords" id="meta_keywords" class="form-control" multiple="multiple">
                                <option value="AL">Alabama</option>
                                <option value="WY">Wyoming</option>
                                <option value="AM">America</option>
                                <option value="CA">Canada</option>
                                <option value="RU">Russia</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Submit</button>
                        <button class="btn btn-light">Cancel</button>
                      </form>
                    </div>
                </div>
            </x-slot>
        </x-admin.templates.tab-component>
    </div>
@endsection
