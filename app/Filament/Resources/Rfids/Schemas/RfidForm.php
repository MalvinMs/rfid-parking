<?php

namespace App\Filament\Resources\Rfids\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RfidForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        TextInput::make('uid')
          ->label('RFID UID')
          ->placeholder('e.g., A1B2C3D4')
          ->required()
          ->unique(ignoreRecord: true)
          ->maxLength(255),

        TextInput::make('name')
          ->label('Device Name')
          ->placeholder('e.g., Gate A1')
          ->maxLength(255),

        TextInput::make('owner_name')
          ->label('Owner Name')
          ->placeholder('Vehicle owner name')
          ->maxLength(255),

        TextInput::make('vehicle_number')
          ->label('Vehicle Number')
          ->placeholder('e.g., B 1234 ABC')
          ->maxLength(255),

        TextInput::make('phone')
          ->label('Phone Number')
          ->tel()
          ->maxLength(255),

        Textarea::make('notes')
          ->label('Notes')
          ->rows(3)
          ->maxLength(1000),

        Toggle::make('is_active')
          ->label('Active')
          ->default(true),
      ]);
  }
}
