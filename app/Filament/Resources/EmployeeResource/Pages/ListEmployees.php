<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
  protected static string $resource = EmployeeResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    return [
      'All' => Tab::make('All'),
      'This Weeks' => Tab::make('This Weeks')->modifyQueryUsing(fn(Builder $query) => $query->where('date_hired', '>=', now()->subWeek())),
    ];
  }
}
