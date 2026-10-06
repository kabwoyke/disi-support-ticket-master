<?php

namespace App\Filament\Resources\TicketCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category_name')
                    ->required(),
            ]);
    }
}
