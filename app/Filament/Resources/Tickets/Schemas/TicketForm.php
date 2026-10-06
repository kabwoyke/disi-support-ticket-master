<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                Select::make('categoryId')
                    ->relationship('category' , 'category_name')
                    ->preload()
                    ->label('Category')
                    ->required(),
                Select::make('priority')
                    ->options([
                        'LOW' => 'LOW',
                        'HIGH' =>'HIGH',
                        'MODERATE' => 'MODERATE'
                    ])
                    ->required()
                    ->default('LOW'),
                Select::make('equipmentId')
                    ->relationship('equipment' , 'name')
                    ->preload()
                    ->label('Equipment')
                    ->required(),
                Select::make('departmentId')
                    ->relationship('department' , 'department_name')
                    ->label('Department')
                    ->required(),

                Select::make('deskId')
                    ->relationship('desk' , 'desk_name')
                    ->preload()
                    ->label('Desk')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('attachment_url')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->required()
                    ->options([
                        "OPEN" => "OPEN",
                        "COLSED" => "CLOSED",
                        "IN-PROGRESS" => "IN-PROGRESS",
                        "RESOLVED" => "RESOLVED"
                    ])
                    ->default('OPEN'),
            ]);
    }
}
