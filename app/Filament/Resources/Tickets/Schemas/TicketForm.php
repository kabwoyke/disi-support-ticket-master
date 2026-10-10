<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Equipment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('subject')
                    ->required()
                    ->columnSpanFull(),
                Select::make('userId')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Raised by')
                    ->required(),
                Select::make('categoryId')
                    ->relationship('category', 'category_name')
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('equipmentId', null))
                    ->label('Category')
                    ->required(),
                Select::make('equipmentId')
                    ->label('Equipment')
                    ->options(fn (Get $get) => Equipment::query()
                        ->when($get('categoryId'), fn ($q, $id) => $q->where('categoryId', $id))
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('departmentId')
                    ->relationship('department', 'department_name')
                    ->preload()
                    ->searchable()
                    ->label('Department')
                    ->required(),
                Select::make('deskId')
                    ->relationship('desk', 'desk_name')
                    ->preload()
                    ->searchable()
                    ->label('Desk')
                    ->required(),
                Select::make('priority')
                    ->options([
                        'LOW' => 'Low',
                        'MODERATE' => 'Moderate',
                        'HIGH' => 'High',
                    ])
                    ->required()
                    ->default('LOW'),
                Select::make('status')
                    ->required()
                    ->options([
                        'OPEN' => 'Open',
                        'IN-PROGRESS' => 'In progress',
                        'RESOLVED' => 'Resolved',
                        'CLOSED' => 'Closed',
                    ])
                    ->default('OPEN'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('attachment_url')
                    ->label('Attachments')
                    ->multiple()
                    ->disk('public')
                    ->directory('attachments/tickets')
                    ->downloadable()
                    ->openable()
                    ->default([])
                    ->columnSpanFull(),
            ]);
    }
}
