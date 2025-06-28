<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;
use App\Models\Tenant;
use App\Models\Bill;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Textarea;



class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                // Select the tenant
                Select::make('tenant_id')
                    ->label('Select Tenant')
                    ->relationship('tenant', 'tenant_name')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $tenant = \App\Models\Tenant::with('house')->find($state);

                        if ($tenant && $tenant->house) {
                            $rent = $tenant->house->rent_amount;

                            // Get bills for current month
                            $bills = \App\Models\Bill::where('tenant_id', $tenant->id)
                                ->whereMonth('bill_month', now()->month)
                                ->whereYear('bill_month', now()->year)
                                ->first();

                            $billTotal = $bills
                                ? ($bills->water ?? 0) + ($bills->trash ?? 0) + ($bills->electricity ?? 0) + ($bills->internet ?? 0)
                                : 0;

                            $set('total_amount', $rent + $billTotal);
                            $set('rent_only', $rent);
                            $set('bill_only', $billTotal);
                        } else {
                            $set('total_amount', null);
                            $set('rent_only', null);
                            $set('bill_only', null);
                        }
                    }),

                // Read-only breakdown fields
                TextInput::make('rent_only')
                    ->label('House Rent (KES)')
                    ->disabled(),

                TextInput::make('bill_only')
                    ->label('Bills Total (KES)')
                    ->disabled(),

                TextInput::make('total_amount')
                    ->label('Total Amount (KES)')
                    ->disabled(),

                // Other fields
                DatePicker::make('invoice_date')
                    ->default(now())
                    ->required(),

                DatePicker::make('due_date')
                    ->default(now()->addDays(10))
                    ->required(),

                Textarea::make('comment')
                    ->placeholder('Optional note'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //invoice view
                TextColumn::make('invoice_number')->label('Invoice #'),
                TextColumn::make('tenant.tenant_name')->label('Tenant'),
                TextColumn::make('tenant.house.house_name')->label('House'),
                TextColumn::make('total_amount')->money('KES'),
                TextColumn::make('invoice_date')->date(),
                TextColumn::make('due_date')->date(),
            ])
            ->filters([
                //filters for invoices
                Filter::make('bill_month')
                    ->label('Filter by Month & Year')
                    ->form([
                        DatePicker::make('month')->label('Pick Month'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['month'])) {
                            $query->whereMonth('invoice_date', $data['month']->format('m'))
                                ->whereYear('invoice_date', $data['month']->format('Y'));
                        }
                    }),
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


    public static function beforeCreate($record)
    {
        // Generate unique invoice number
        $lastInvoice = \App\Models\Invoice::latest('id')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        $record->invoice_number = 'INV-' . $nextId;

        // Calculate rent + bills
        $tenant = Tenant::find($record->tenant_id);
        $houseRent = $tenant->house->rent_amount ?? 0;

        $bill = Bill::where('tenant_id', $tenant->id)
            ->whereMonth('bill_month', now()->month)
            ->whereYear('bill_month', now()->year)
            ->first();

        $billTotal = $bill ? ($bill->water_bill + $bill->trash_bill + $bill->internet_bill) : 0;

        $record->total_amount = $houseRent + $billTotal;
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
