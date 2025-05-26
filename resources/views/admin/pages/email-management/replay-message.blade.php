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
                                    <h4 class="card-title">Balas Pesan Dari : {{ $message->name }}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex w-100 align-items-center justify-content-end w-50" style="gap: 10px">
                                    <a href="{{ route('admin.email.show', $message->id) }}" id="create__message" class="btn btn-sm btn-danger"><i class="ri-arrow-left-line me-2"></i> Kembali</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <form class="row" method="POST" action="{{ route('admin.email.send_replay_message', $message->message_id) }}">
                            @csrf
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama : </label>
                                    <input type="text" class="form-control" value="{{ $message->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">To Email : </label>
                                    <input type="email" class="form-control" value="{{ $message->email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Email : </label>
                                    <input type="text" class="form-control" value="{{ $from_email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subject : </label>
                                    <input type="text" class="form-control" value="{{ $message->subject }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Message : </label>
                                    <textarea name="message" class="form-control" id="message" placeholder="Masukkan pesan disini..." style="height: 200px;line-height: 20px"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary"><i class="ri-mail-send-line me-2"></i> Kirim</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection