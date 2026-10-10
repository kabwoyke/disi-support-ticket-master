<?php

namespace App\Filament\Resources\SupportTeams\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
                Select::make('ticket_category_id')
                    ->label('Specialty (ticket category)')
                    ->relationship('category', 'category_name')
                    ->preload()
                    ->searchable()
                    ->required(),
                TextInput::make('max_ticket_capacity')
                    ->label('Ticket Capacity')
                    ->numeric()
                    ->minValue(1)
                    ->default(3)
                    ->required(),
                TextInput::make('ticket_count')
                    ->label('Ticket Count')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                FileUpload::make('profile_picture')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('profile-pictures')
                    ->required(),
                Toggle::make('available')
                    ->default(true),
            ]);
    }
}
