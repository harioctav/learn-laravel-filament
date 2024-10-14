<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class CountryResource extends Resource
{
  protected static ?string $model = Country::class;
  protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
  protected static ?string $navigationLabel = 'Country';
  protected static ?string $modelLabel = 'Employees Country';
  protected static ?string $navigationGroup = 'System Management';
  protected static ?int $navigationSort = 1;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Country Data')
          ->schema([
            TextInput::make('name')
              ->required()
              ->maxLength(255),
            TextInput::make('code')
              ->required()
              ->numeric()
              ->maxLength(3),
            TextInput::make('phonecode')
              ->required()
              ->numeric()
              ->maxLength(5),
          ])->columns(3)
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('code')->sortable()->searchable(),
        TextColumn::make('name')->sortable()->searchable(),
        TextColumn::make('phonecode')->sortable()->searchable(),
        TextColumn::make('created_at')->dateTime(),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function infoList(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        InfoSection::make('Country Info')
          ->description('Details of the Country')
          ->schema([
            TextEntry::make('code')
              ->label('Code'),
            TextEntry::make('name')
              ->label('Country Name'),
            TextEntry::make('phonecode')
              ->label('Country Phone Code'),
          ])->columns(3),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      // EmployeesRelationManager::class,
      // StatesRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListCountries::route('/'),
      'create' => Pages\CreateCountry::route('/create'),
      'edit' => Pages\EditCountry::route('/{record}/edit'),
    ];
  }
}
