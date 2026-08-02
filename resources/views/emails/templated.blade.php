@extends('emails.layouts.mobile-brand')

@section('headline', $headline ?? config('app.name'))

@section('content')
    <div style="font-size:14px;line-height:1.7;color:#334155;">
        {!! $body !!}
    </div>
@endsection
