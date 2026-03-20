<?php

namespace App\Filament\Resources\Rfids\Pages;

use App\Filament\Resources\Rfids\RfidResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRfids extends ListRecords
{
  protected static string $resource = RfidResource::class;

  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make(),
    ];
  }

  protected function getTableQuery(): Builder
  {
    return parent::getTableQuery()->withCount('parkingSessions');
  }
}
