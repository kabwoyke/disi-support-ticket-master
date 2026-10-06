<?php

namespace App\Filament\Resources\Tickets\Schemas;

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
                TextEntry::make('categoryId')
                    ->numeric(),
                TextEntry::make('priority'),
                TextEntry::make('equipmentId')
                    ->numeric(),
                TextEntry::make('departmentId')
                    ->numeric(),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('attachment_url')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
