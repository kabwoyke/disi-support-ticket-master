<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

    $openTickets = count(Ticket::where('status' , '=' ,'OPEN' , 'and')->get());
    $closedTickets = count(Ticket::where('status' , '=' ,'CLOSED' , 'and')->get());
        return [
            //

            Stat::make('Open Tickets' , $openTickets)
                ->description("Number of open tickets")
                ->descriptionIcon('heroicon-m-ticket')
                  ->color('danger'),


            Stat::make('Closed Tickets' , $closedTickets)
                ->description("Number of closed tickets")
                ->color('success'),

            Stat::make('In Progress' , $closedTickets)
                ->description("Number of In Progress tickets")
                ->color('warning'),
        ];
    }
}
