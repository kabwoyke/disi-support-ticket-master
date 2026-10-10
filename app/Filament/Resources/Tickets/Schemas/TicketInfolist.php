<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('subject')
                    ->columnSpanFull(),
                TextEntry::make('user.name')
                    ->label('Raised by'),
                TextEntry::make('category.category_name')
                    ->label('Category'),
                TextEntry::make('equipment.name')
                    ->label('Equipment'),
                TextEntry::make('department.department_name')
                    ->label('Department'),
                TextEntry::make('desk.desk_name')
                    ->label('Desk'),
                TextEntry::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'HIGH' => 'danger',
                        'MODERATE' => 'warning',
                        default => 'gray',
                    }),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'OPEN' => 'danger',
                        'IN-PROGRESS' => 'warning',
                        'RESOLVED' => 'info',
                        'CLOSED' => 'success',
                        default => 'gray',
                    }),
                TextEntry::make('description')
                    ->columnSpanFull(),
                ImageEntry::make('attachment_url')
                    ->label('Attachments')
                    ->disk('public')
                    ->placeholder('No attachments')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
