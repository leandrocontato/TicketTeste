<?php

namespace App\Http\Controllers;

use App\Thrust\Metrics\NewTicketsByMonthMetric;
use App\Thrust\Metrics\NewTicketsMetric;
use App\Thrust\Metrics\SolvedMetric;
use App\Thrust\Metrics\TeamTicketsMetric;
use App\Thrust\Metrics\TicketsCountMetric;
use App\Thrust\Metrics\TicketTypeMetric;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function analytics()
    {
        return view('reports.analytics', [
           'metrics' => [
               (new TicketsCountMetric),
               (new SolvedMetric),
               (new NewTicketsMetric),
               (new NewTicketsByMonthMetric),
               (new TicketTypeMetric),
               (new TeamTicketsMetric),
           ],
        ]);
    }
}
