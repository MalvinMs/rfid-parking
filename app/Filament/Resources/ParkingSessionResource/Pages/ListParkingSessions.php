<?php

namespace App\Filament\Resources\ParkingSessionResource\Pages;

use App\Filament\Resources\ParkingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParkingSessions extends ListRecords
{
  protected static string $resource = ParkingSessionResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }
}
