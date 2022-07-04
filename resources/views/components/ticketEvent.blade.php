<div class="ticketEvent p1 mb1">
    <div class="">
        {{ $event->author()->name }}
        •
        {{ $event->created_at->diffForHumans() }}
    </div>
</div>
