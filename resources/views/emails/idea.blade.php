@extends('emails.layout')
@section('body')
        <div style="border-bottom:1px solid #efefef; padding-bottom:10px; margin-left:20px; margin-top:20px;">
                <b> {{ $idea->requester->name }}</b><br>
                <span style="color:gray">{{ $idea->created_at->toDateTimeString() }}</span><br>
        </div>
        <div style="margin-top:40px">
                <a href="{{$url}}"></a>
        </div>
@endsection
