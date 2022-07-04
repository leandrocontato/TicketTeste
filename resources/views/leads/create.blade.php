@extends('layouts.app')
@section('content')
    <div class="description comment">
        <div class="breadcrumb">
            <a href="{{ route('leads.index') }}"> {{ trans_choice('lead.lead',2) }} </a>
        </div>
    </div>
    {{ Form::open(["url" => route("leads.store")]) }}
    <div class="comment new-comment">
        @include('components.lead.fields', ["lead" => new App\Lead( request()->all() )])
        <table class="no-padding maxw600">
            @include('components.assignTeamField')
            <tr><td> {{__('ticket.comment') }}</td><td colspan="7"><textarea name="body"> {{ request('body') }}</textarea></td></tr>
            <tr><td colspan="3"><button class="uppercase"> @icon(plus) {{ __('ticket.new') }}</button></td></tr>
        </table>
        {{ Form::close() }}
    </div>
@endsection
