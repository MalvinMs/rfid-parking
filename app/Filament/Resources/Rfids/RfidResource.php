<?php

namespace App\Filament\Resources\Rfids;

use App\Filament\Resources\Rfids\Pages\CreateRfid;
use App\Filament\Resources\Rfids\Pages\EditRfid;
use App\Filament\Resources\Rfids\Pages\ListRfids;
use App\Filament\Resources\Rfids\Schemas\RfidForm;
use App\Filament\Resources\Rfids\Tables\RfidsTable;
use App\Models\Rfid;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RfidResource extends Resource
{
  protected static ?string $model = Rfid::class;

  protected static ?string $navigationLabel = 'RFID Devices';

  protected static ?string $modelLabel = 'RFID Device';

  protected static ?string $pluralModelLabel = 'RFID Devices';

  protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

  public static function form(Schema $schema): Schema
  {
    return RfidForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return RfidsTable::configure($table);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListRfids::route('/'),
      'create' => CreateRfid::route('/create'),
      'edit' => EditRfid::route('/{record}/edit'),
    ];
  }
}
