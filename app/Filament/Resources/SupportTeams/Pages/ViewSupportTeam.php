<?php

namespace App\Filament\Resources\SupportTeams\Pages;

use App\Filament\Resources\SupportTeams\SupportTeamResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportTeam extends ViewRecord
{
    protected static string $resource = SupportTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
