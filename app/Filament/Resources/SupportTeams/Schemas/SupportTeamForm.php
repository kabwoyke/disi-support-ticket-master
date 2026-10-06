<?php

namespace App\Filament\Resources\SupportTeams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupportTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('ticket_count')
                    ->label('Ticket Count')
                    ->email()
                    ->required(),
                TextInput::make('max_ticket_capacity')
                    ->label('Ticket Capacity')
                    ->email()
                    ->required(),
                Textarea::make('profile_picture')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('specialty')
                    ->required(),
                Toggle::make('available')
                    ->required(),
            ]);
    }
}
