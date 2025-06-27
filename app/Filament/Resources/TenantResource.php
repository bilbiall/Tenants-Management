<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

//for the panel
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

use Filament\Tables\Columns\TextColumn;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //form input for new tenant
                /*Select::make('house_id')
                    ->label('House')
                    ->relationship('house', 'house_name', modifyQueryUsing: function ($query) {
                        $query->where('house_status', 'Vacant');
                    })
                    ->searchable()
                    ->required(),*/
                Select::make('house_id')
                    ->label('House')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\House::query()
                            ->where('house_name', 'like', "%{$search}%")
                            ->pluck('house_name', 'id');
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        return \App\Models\House::find($value)?->house_name;
                    })
                    ->required(),

                TextInput::make('tenant_name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone_number')->required(),
                DatePicker::make('date_admitted')->default(now())->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //for the display in tenants page
                TextColumn::make('tenant_name')->searchable(),
                TextColumn::make('house.house_name')->label('House'),
                TextColumn::make('house.rent_amount')->money('KES'),
                TextColumn::make('house.house_status')->label('Status'),
                TextColumn::make('date_admitted')->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
