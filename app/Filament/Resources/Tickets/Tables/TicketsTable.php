<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'equipment', 'department', 'user']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->subject),
                TextColumn::make('user.name')
                    ->label('Raised by')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.category_name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'HIGH' => 'danger',
                        'MODERATE' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department.department_name')
                    ->label('Department')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'OPEN' => 'danger',
                        'IN-PROGRESS' => 'warning',
                        'RESOLVED' => 'info',
                        'CLOSED' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'OPEN' => 'Open',
                        'IN-PROGRESS' => 'In progress',
                        'RESOLVED' => 'Resolved',
                        'CLOSED' => 'Closed',
                    ])
                    ->multiple(),
                SelectFilter::make('priority')
                    ->options(['LOW' => 'Low', 'MODERATE' => 'Moderate', 'HIGH' => 'High'])
                    ->multiple(),
                SelectFilter::make('categoryId')
                    ->label('Category')
                    ->relationship('category', 'category_name')
                    ->preload(),
                SelectFilter::make('departmentId')
                    ->label('Department')
                    ->relationship('department', 'department_name')
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markClosed')
                        ->label('Mark as closed')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'CLOSED']))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
