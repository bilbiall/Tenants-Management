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

use App\Models\User; // Afor linking tenant to user
use App\Helpers\SmsHelper; // ✅ for sms
use Illuminate\Support\Str; // For generating a temporary password


class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                /*Select::make('house_id')
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
                    ->required(),*/
                    /*Select::make('house_id')
                        ->label('House')
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return \App\Models\House::query()
                                ->where('house_status', 'Vacant') // ✅ Only vacant
                                ->where('house_name', 'like', "%{$search}%")
                                ->pluck('house_name', 'id');
                        })
                        ->getOptionLabelUsing(function ($value): ?string {
                            return \App\Models\House::find($value)?->house_name;
                        })
                        ->required(),*/

                //cleaner alternan=tive
                //select user
                /*Select::make('user_id')
                    ->label('Linked User')
                    ->relationship('user', 'name') // assumes User model has a 'name' column
                    ->searchable(),
                    ->required(),*/

                //select user or alt create new user
                /*Select::make('user_id')
                    ->label('Linked User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required()->unique(User::class, 'email'),
                        TextInput::make('password')->password()->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => bcrypt($data['password']),
                        ]);
                    }),*/
                //new user with the role setup auto and the sms sent with password and login
                Select::make('user_id')
                    ->label('Linked User')
                    ->relationship('user', 'full_label') //drop down to be John Doe (john@example.com)
                    //->relationship('user', 'name') // or 'email' if you use email
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) { // ✅ Added to enable search by name or email
                        return \App\Models\User::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(function ($user) {
                                return [$user->id => $user->full_label]; // ✅ returns full_label like "John Doe (john@example.com)"
                            });
                    }) // ✅ End of addition
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required()->unique(User::class, 'email'),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $password = Str::random(8); // Generate temp password

                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone_number' => $data['phone_number'],
                            'password' => bcrypt($password),
                            'role' => 'tenant', // ✅ Automatically assign tenant role
                        ]);

                        // ✅ Send SMS
                        $message = "Hi {$user->name}, your tenant account has been created. Login with Email: {$user->email}, Password: {$password} - " . config('app.name');
                        SmsHelper::sendSms($user->phone_number, $message);

                        return $user->getKey();
                    }),


                Select::make('house_id')
                    ->label('House')
                    ->relationship('house', 'house_name', modifyQueryUsing: fn ($query) =>
                        $query->where('house_status', 'Vacant')
                    )
                    ->searchable()
                    ->required(),



                TextInput::make('tenant_name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone_number')->required(),
                /*Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name') // or 'email' if you want email shown
                    ->searchable()
                    ->required(), // Optional if you want to always assign user*/

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
                /*TextColumn::make('latestInvoice.balance')
                    ->label('Balance')
                    ->money('KES')
                    ->color(function ($state) {
                        if ($state === null) return 'secondary'; // No invoice yet
                        if ($state == 0) return 'success';
                        if ($state < 0) return 'warning'; // Overpaid
                        return 'danger'; // Still owing
                    }),*/
                //include color for overpaid
                TextColumn::make('latestInvoice.balance')
                    ->label('Balance')
                    ->money('KES')
                    ->color(function ($state) {
                        if ($state === null) {
                            return 'secondary'; // No invoice yet
                        } elseif ($state == 0) {
                            return 'success'; // Fully paid
                        } elseif ($state < 0) {
                            return 'warning'; // Overpaid
                        } else {
                            return 'danger'; // Still has balance due
                        }
                    }),


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
