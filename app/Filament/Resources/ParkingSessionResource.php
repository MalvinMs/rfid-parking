<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParkingSessionResource\Pages\CreateParkingSession;
use App\Filament\Resources\ParkingSessionResource\Pages\EditParkingSession;
use App\Filament\Resources\ParkingSessionResource\Pages\ListParkingSessions;
use App\Models\ParkingSession;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParkingSessionResource extends Resource
{
  protected static ?string $model = ParkingSession::class;

  protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
  public static function form(Schema $schema): Schema
  {
    return $schema
      ->schema([
        TextInput::make('rfid_uid')
          ->label('RFID UID')
          ->required()
          ->maxLength(255),

        TextInput::make('rfid.name')
          ->label('RFID Device')
          ->disabled(),

        TextInput::make('rfid.vehicle_number')
          ->label('Vehicle Number')
          ->disabled(),

        DateTimePicker::make('check_in_at')
          ->label('Check In Time')
          ->required()
          ->disabled(),

        DateTimePicker::make('expires_at')
          ->label('Expiry Time')
          ->required()
          ->disabled(),

        DateTimePicker::make('check_out_at')
          ->label('Check Out Time'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('rfid.name')
          ->label('Device Name')
          ->searchable()
          ->sortable(),

        TextColumn::make('rfid_uid')
          ->label('RFID UID')
          ->searchable()
          ->sortable(),

        TextColumn::make('rfid.owner_name')
          ->label('Owner')
          ->searchable()
          ->sortable(),

        TextColumn::make('rfid.vehicle_number')
          ->label('Vehicle Number')
          ->searchable()
          ->sortable(),

        TextColumn::make('check_in_at')
          ->label('Check In')
          ->dateTime()
          ->sortable(),

        TextColumn::make('expires_at')
          ->label('Expires At')
          ->dateTime()
          ->sortable(),

        BadgeColumn::make('status')
          ->label('Status')
          ->getStateUsing(fn(ParkingSession $record) => $record->status)
          ->colors([
            'success' => 'NORMAL',
            'danger' => 'OVERTIME',
            'gray' => 'OUT',
          ]),

        TextColumn::make('remaining_minutes')
          ->label('Remaining (min)')
          ->getStateUsing(fn(ParkingSession $record) => $record->remaining_minutes),

        TextColumn::make('check_out_at')
          ->label('Check Out')
          ->dateTime()
          ->sortable(),

        TextColumn::make('created_at')
          ->label('Created')
          ->dateTime()
          ->sortable(),
      ])
      ->filters([
        Filter::make('active')
          ->label('Active Sessions')
          ->query(fn(Builder $query) => $query->whereNull('check_out_at'))
          ->toggle(),

        Filter::make('overtime')
          ->label('Overtime Sessions')
          ->query(
            fn(Builder $query) => $query
              ->whereNull('check_out_at')
              ->where('expires_at', '<', now())
          )
          ->toggle(),

        Filter::make('checked_out')
          ->label('Checked Out')
          ->query(fn(Builder $query) => $query->whereNotNull('check_out_at'))
          ->toggle(),
      ])
      ->recordActions([
        DeleteAction::make(),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ])
      ->defaultSort('check_in_at', 'desc')
      ->striped();
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListParkingSessions::route('/'),
      'create' => CreateParkingSession::route('/create'),
      'edit' => EditParkingSession::route('/{record}/edit'),
    ];
  }
}
