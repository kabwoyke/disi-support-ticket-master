<?php

namespace App\Filament\Resources\TicketCategories\Pages;

use App\Filament\Resources\TicketCategories\TicketCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTicketCategory extends ViewRecord
{
    protected static string $resource = TicketCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
