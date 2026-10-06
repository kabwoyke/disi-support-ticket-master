<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('categoryId')
                    ->relationship('category' , 'category_name')
                    ->preload()
                    ->searchable()
                    ->label('Category')
                    ->required(),
            ]);
    }
}
