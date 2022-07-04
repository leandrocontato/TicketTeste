<?php

namespace App\Http\Controllers;

use App\Ticket;
use Illuminate\Http\Response;

class RequesterTicketsController extends Controller
{
    public function show($public_token)
    {
        $ticket = Ticket::findWithPublicToken($public_token);

        return view('requester.tickets.show', ['ticket' => $ticket]);
    }
}
