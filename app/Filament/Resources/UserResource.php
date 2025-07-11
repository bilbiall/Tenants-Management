<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

//for the input form for users
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;


//use App\Filament\Resources\AdminResource\Pages\SendNotification;
use App\Filament\Resources\UserResource\Pages\SendNotification;




//to display columns in users
use Filament\Tables\Columns\TextColumn;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //user form
                TextInput::make('name')->required(),
                TextInput::make('email')->email(),
                //TextInput::make('password')->password()
                //to enable password updates
                TextInput::make('password')
                    ->password()
                    ->required()
                    //->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))

                    ->dehydrated(fn ($state) => filled($state)) // Only update if filled
                    ->label('Password'),

                // ✅ Role selection dropdown
                Select::make('role')
                    ->required()
                    ->label('User Role')
                    ->options([
                        'admin' => 'Admin',
                        'caretaker' => 'Caretaker',
                        'tenant' => 'Tenant',
                    ])
                    ->native(false), // optional: to use searchable dropdown
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //column heads
                TextColumn::make('name'),
                TextColumn::make('email')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'sendNotification' => SendNotification::route('/send-notification'), // your custom page


        ];
    }
}
