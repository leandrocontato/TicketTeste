<?php

namespace App\Http\Controllers;

use App\Ticket;

class TicketsMergeController extends Controller
{
    public function index()
    {
        return view('tickets.merge');
    }
}
