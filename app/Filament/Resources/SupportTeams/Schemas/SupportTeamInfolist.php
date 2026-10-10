<?php

namespace App\Filament\Resources\SupportTeams\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupportTeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('avatar_url')
                    ->label('Photo')
                    ->circular(),
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('phone_number'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('category.category_name')
                    ->label('Specialty'),
                TextEntry::make('ticket_count')
                    ->label('Active tickets'),
                TextEntry::make('max_ticket_capacity')
                    ->label('Ticket capacity'),
                IconEntry::make('available')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
