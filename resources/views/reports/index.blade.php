@extends('layouts.app')
@section('content')
    <div class="description">
        <h3>{{ trans_choice('report.report', 2) }}</h3>
    </div>
    <div class="description">
        {{ Form::open( ["url" => route('reports.index'), 'method' => 'GET'] ) }}
        @include('components.datesFilter')
        {{ Form::close() }}
    </div>
    <table class="striped">
        <thead>
            <tr>
                <th></th>
                <th> {{ trans_choice('ticket.ticket',2) }}      </th>
                <th> {{ trans_choice('ticket.unanswered',2) }}  </th>
                <th> {{ trans_choice('ticket.open',2) }}        </th>
                <th> {{ trans_choice('ticket.solved',2) }}      </th>
                <th> {{ __('report.firstReplyTime') }}          </th>
                <th> {{ __('report.solveTime') }}               </th>
            </tr>
        </thead>
        <tr>
            <td> {{ __('user.you') }} </td>
            <td>  {{ $repository->tickets( auth()->user() ) }}   </td>
            <td>  {{ $repository->unansweredTickets( auth()->user() ) }}   </td>
            <td>  {{ $repository->openTickets( auth()->user() ) }}   </td>
            <td>  {{ $repository->solvedTickets( auth()->user() ) }}   </td>
        </tr>
        @foreach(auth()->user()->teams as $team)
        <tr>
            <td> {{ $team->name }} </td>
            <td>  {{ $repository->tickets( $team ) }}   </td>
            <td>  {{ $repository->unansweredTickets( $team ) }}   </td>
            <td>  {{ $repository->openTickets( $team ) }}   </td>
            <td>  {{ $repository->solvedTickets( $team ) }}   </td>
        </tr>
        @endforeach
        <tr>
            <td> {{ __('ticket.all') }} </td>
            <td>  @if(auth()->user()->admin ){{ $repository->tickets( ) }}  @endif </td>
            <td>  @if(auth()->user()->admin ){{ $repository->unansweredTickets( ) }}  @endif </td>
            <td>  @if(auth()->user()->admin ){{ $repository->openTickets( ) }}  @endif </td>
            <td>  @if(auth()->user()->admin ){{ $repository->solvedTickets( ) }}  @endif </td>
        </tr>
    </table>
@endsection
