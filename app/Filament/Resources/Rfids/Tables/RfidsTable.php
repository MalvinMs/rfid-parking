<?php

namespace App\Filament\Resources\Rfids\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RfidsTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('uid')
          ->label('RFID UID')
          ->searchable()
          ->sortable(),

        TextColumn::make('name')
          ->label('Device Name')
          ->searchable()
          ->sortable(),

        TextColumn::make('owner_name')
          ->label('Owner')
          ->searchable()
          ->sortable(),

        TextColumn::make('vehicle_number')
          ->label('Vehicle Number')
          ->searchable()
          ->sortable(),

        TextColumn::make('phone')
          ->label('Phone')
          ->searchable()
          ->sortable(),

        ToggleColumn::make('is_active')
          ->label('Active')
          ->sortable(),

        TextColumn::make('parking_sessions_count')
          ->label('Total Sessions')
          ->counts('parkingSessions')
          ->sortable(),

        TextColumn::make('created_at')
          ->label('Created')
          ->dateTime('Y-m-d H:i')
          ->sortable(),
      ])
      ->filters([
        TernaryFilter::make('is_active')
          ->label('Active')
          ->nullable(),
      ])
      ->recordActions([
        EditAction::make(),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ]);
  }
}
