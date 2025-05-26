@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex w-100 justify-content-between">
                                    <h4 class="card-title">Detail Pesan</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex w-100 align-items-center justify-content-end w-50" style="gap: 10px">
                                    <a href="{{ route('admin.email.index') }}" id="create__message" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line me-2"></i> Kembali</a>
                                    <a href="{{ route('admin.email.replay_message', $message->message_id) }}" class="btn btn-sm btn-success"><i class="ri-corner-up-left-double-line me-2"></i> Balas</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-4">
                        <div class="accordion accordion-flush" id="accordionMessage-{{ $message->id }}">
                            <div class="accordion-item" style="border: 1px solid #ccc">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                       <span class="me-4"><strong>[{{ $message->name }} - {{ $message->email }}]</strong></span> <span>Subject : <strong>{{ $message->subject }}</strong></span>
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionMessage-{{ $message->id }}">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-4">
                                                <span class="d-block mb-2" style="font-size: 0.9em">Dikirim pada : <strong>{{ \Carbon\Carbon::parse($message->created_at)->diffForHumans() }}</strong></span>
                                                <span class="d-block" style="font-size: 0.9em">Ke Email : <a href="mailto:{{ $message->to_email }}">{{ $message->to_email }}</a></span>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="title">Nama : </label>
                                                            <input type="text" class="form-control" value="{{ $message->name }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="title">Email : </label>
                                                            <input type="email" class="form-control" value="{{ $message->email }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Subject : </label>
                                                            <input type="text" class="form-control" value="{{ $message->subject }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="title">Message : </label>
                                                            <textarea class="form-control" style="height: 200px" readonly>{{ $message->message }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- inbound email --}}
                            @if(($message?->inBoundmail?->count() ?? 0) > 0)
                                @foreach($message->inBoundmail as $key => $value)
                                    <div class="accordion-item" style="border: 1px solid #ccc">
                                        <h2 class="accordion-header" id="inbound-heading-{{ $value->id }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#inbound-controls-{{ $value->id }}" aria-expanded="false" aria-controls="inbound-controls-{{ $value->id }}">
                                            <span class="me-4"><strong>[{{ $value->name }} - {{ $value->email }}]</strong></span> <span>Subject : <strong>{{ $value->subject }}</strong></span>
                                            </button>
                                        </h2>
                                        <div id="inbound-controls-{{ $value->id }}" class="accordion-collapse collapse" aria-labelledby="inbound-heading-{{ $value->id }}" data-bs-parent="#accordionMessage-{{ $message->id }}">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-12 mb-4">
                                                        <span class="d-block mb-2" style="font-size: 0.9em">Dikirim pada : <strong>{{ \Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</strong></span>
                                                        <span class="d-block" style="font-size: 0.9em">Ke Email : <a href="mailto:{{ $value->to_email }}">{{ $value->to_email }}</a></span>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="title">Nama : </label>
                                                                    <input type="text" class="form-control" value="{{ $value->name }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="title">Email : </label>
                                                                    <input type="email" class="form-control" value="{{ $value->email }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label>Subject : </label>
                                                                    <input type="text" class="form-control" value="{{ $value->subject }}" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="title">Message : </label>
                                                                    <textarea class="form-control" style="height: 200px" readonly>{!! strip_tags($value->message) !!}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    
</script>
@endsection