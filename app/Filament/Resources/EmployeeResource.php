<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker as FilterDatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;
use Filament\Tables\Filters\Indicator;

class EmployeeResource extends Resource
{
  protected static ?string $model = Employee::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-group';
  protected static ?string $navigationGroup = 'Employee Management';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Locations')
          ->description('Put the user location details in')
          ->schema([
            Select::make('country_id')
              ->label('Country')
              ->options(Country::all()
                ->pluck('name', 'id')
                ->toArray())
              ->required()
              ->searchable()
              ->preload()
              ->live()
              ->reactive()
              ->afterStateUpdated(function (callable $set) {
                $set('state_id', null);
                $set('city_id', null);
              }),
            Select::make('state_id')
              ->label('State')
              ->required()
              ->options(function (callable $get) {
                $country = Country::find($get('country_id'));
                if (!$country) {
                  return null;
                }
                return $country->states->pluck('name', 'id');
              })
              ->reactive()
              ->searchable()
              ->preload()
              ->live()
              ->afterStateUpdated(fn(callable $set) => $set('city_id', null)),
            Select::make('city_id')
              ->label('City')
              ->required()
              ->searchable()
              ->preload()
              ->live()
              ->options(function (callable $get) {
                $state = State::find($get('state_id'));
                if (!$state) {
                  return null;
                }
                return $state->cities->pluck('name', 'id');
              }),
            Select::make('department_id')
              ->relationship(name: 'department', titleAttribute: 'name')
              ->searchable()
              ->preload()
              ->searchable()
              ->preload()
              ->live()
              ->required(),
          ])->columns(2),

        Section::make('User Name')
          ->description('Put the user name details in')
          ->schema([
            TextInput::make('first_name')
              ->required()
              ->maxLength(255),
            TextInput::make('middle_name')
              ->required()
              ->maxLength(255),
            TextInput::make('last_name')
              ->required()
              ->maxLength(255),
          ])->columns(3),

        Section::make('User Address')
          ->schema([
            TextInput::make('address')
              ->required()
              ->placeholder('Full Adress')
              ->maxLength(255),
            TextInput::make('zip_code')
              ->required()
              ->maxLength(255),
          ])->columns(),

        Section::make('Dates')
          ->schema([
            DatePicker::make('date_of_birth')
              ->native(false)
              ->maxDate(now())
              ->displayFormat('d/m/Y')
              ->required(),
            DatePicker::make('date_hired')
              ->native(false)
              ->maxDate(now())
              ->displayFormat('d/m/Y')
              ->required(),
          ])->columns(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('country.name')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('city.name')
          ->sortable()
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('state.name')
          ->sortable()
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('department.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('first_name')
          ->searchable(),
        Tables\Columns\TextColumn::make('last_name')
          ->searchable(),
        Tables\Columns\TextColumn::make('middle_name')
          ->searchable(),
        Tables\Columns\TextColumn::make('address')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('zip_code')
          ->searchable(),
        Tables\Columns\TextColumn::make('date_of_birth')
          ->date()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('date_hired')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        SelectFilter::make('Department')
          ->relationship('department', 'name')
          ->searchable()
          ->preload()
          ->label('Filter by Department')
          ->indicator('Department'),
        Filter::make('created_at')
          ->form([
            FilterDatePicker::make('created_from'),
            FilterDatePicker::make('created_until'),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['created_from'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['created_until'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
          ->indicateUsing(function (array $data): array {
            $indicators = [];

            if ($data['created_from'] ?? null) {
              $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                ->removeField('created_from');
            }

            if ($data['created_until'] ?? null) {
              $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                ->removeField('created_until');
            }

            return $indicators;
          })->columnSpan(2)->columns(),
      ], layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
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

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListEmployees::route('/'),
      'create' => Pages\CreateEmployee::route('/create'),
      // 'view' => Pages\ViewEmployee::route('/{record}'),
      'edit' => Pages\EditEmployee::route('/{record}/edit'),
    ];
  }
}
