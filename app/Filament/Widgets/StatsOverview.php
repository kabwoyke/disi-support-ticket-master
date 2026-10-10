<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $counts = Ticket::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            Stat::make('Open Tickets', $counts['OPEN'] ?? 0)
                ->description('Awaiting action')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('danger'),
            Stat::make('In Progress', $counts['IN-PROGRESS'] ?? 0)
                ->description('Currently being worked on')
                ->color('warning'),
            Stat::make('Resolved', $counts['RESOLVED'] ?? 0)
                ->description('Resolved, pending closure')
                ->color('info'),
            Stat::make('Closed Tickets', $counts['CLOSED'] ?? 0)
                ->description('Completed tickets')
                ->color('success'),
        ];
    }
}
