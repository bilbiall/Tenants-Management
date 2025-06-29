<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-s-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Select tenant (only those with unpaid or partial invoices)
            Select::make('tenant_id')
                ->label('Tenant')
                ->relationship('tenant', 'tenant_name')
                ->searchable()
                ->live()
                ->required(),

            // Show invoice number based on selected tenant
            Select::make('invoice_id')
                ->label('Invoice')
                ->options(function (callable $get) {
                    $tenantId = $get('tenant_id');
                    if (!$tenantId) return [];
                    return \App\Models\Invoice::where('tenant_id', $tenantId)
                        ->whereIn('status', ['Unpaid', 'Partial'])
                        ->pluck('invoice_number', 'id');
                })
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                    // 👇 This makes the expected_amount update when an invoice is selected
                    $invoice = \App\Models\Invoice::find($state);
                    if ($invoice) {
                        $set('expected_amount', $invoice->amount);
                    }
                }),

            // Display expected amount (not editable, dynamically filled)
            TextInput::make('expected_amount')
                ->label('Expected Amount')
                ->disabled()
                ->numeric()
                ->reactive(), // 👈 Ensure it updates reactively

            // Show remaining balance from invoice (can be 0 or negative or positive)
            TextInput::make('balance')
                ->label('Remaining Balance (KES)')
                ->disabled()
                ->reactive()
                ->default(function (callable $get) {
                    $invoiceId = $get('invoice_id');
                    if (!$invoiceId) return null;

                    return \App\Models\Invoice::find($invoiceId)?->balance;
                }),



            // Amount paid
            TextInput::make('amount_paid')
                ->label('Amount Paid')
                ->numeric()
                ->required(),

            // Payment reference
            TextInput::make('reference')
                ->label('Payment Reference')
                ->placeholder('e.g. MPESA Ref')
                ->required(),

            // Optional note
            Textarea::make('note')
                ->label('Note'),

            // Optional payment date
            /*DatePicker::make('payment_date')
                ->default(now())
                ->required(),*/
            DatePicker::make('payment_date')
                ->label('Payment Date')
                ->default(now())
                ->required(),

        ]);
                // Select tenant (only those with unpaid or partial invoices)
            /*Select::make('tenant_id')
                ->label('Tenant')
                ->relationship('tenant', 'tenant_name')
                ->searchable()
                ->live()
                ->required(),

            // Show invoice number based on selected tenant
            Select::make('invoice_id')
                ->label('Invoice')
                ->options(function (callable $get) {
                    $tenantId = $get('tenant_id');
                    if (!$tenantId) return [];
                    return \App\Models\Invoice::where('tenant_id', $tenantId)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->pluck('invoice_number', 'id');
                })
                ->required()
                ->live(),

            // Display expected amount (not editable)
            TextInput::make('expected_amount')
                ->label('Expected Amount')
                ->default(function (callable $get) {
                    $invoiceId = $get('invoice_id');
                    if (!$invoiceId) return null;
                    $invoice = \App\Models\Invoice::find($invoiceId);
                    return $invoice?->amount;
                })
                ->disabled(),

            // Amount paid
            TextInput::make('amount_paid')
                ->label('Amount Paid')
                ->numeric()
                ->required(),

            // Payment reference
            TextInput::make('reference')
                ->label('Payment Reference')
                ->required(),

            // Optional note
            Textarea::make('note')
                ->label('Note'),

            // Optional payment date
            DatePicker::make('payment_date')
                ->default(now())
                ->required(),
            ]);*/
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('tenant.tenant_name')->label('Tenant')->searchable(),
                TextColumn::make('invoice.invoice_number')->label('Invoice'),
                TextColumn::make('amount_paid')->money('KES'),
                //TextColumn::make('balance')->label('Balance')->money('KES'),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('KES') // optional, if you want currency formatting
                    ->sortable()
                    ->color(function ($state) {
                        if ($state == 0) {
                            return 'success'; // Green for cleared balance
                        } elseif ($state < 0) {
                            return 'warning'; // Yellow for overpayment
                        }
                        return 'danger'; // Red for unpaid
                    }),

                /*TextColumn::make('payment_date')->date(),*/
                TextColumn::make('payment_date')->label('Payment Date')->date(),

            ])
            ->filters([
                //by month
                Filter::make('payment_month')
                    ->label('Filter by Month & Year')
                    ->form([
                        DatePicker::make('month')->label('Pick Month'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['month'])) {
                            $query->whereMonth('payment_date', \Carbon\Carbon::parse($data['month'])->format('m'))
                                ->whereYear('payment_date', \Carbon\Carbon::parse($data['month'])->format('Y'));
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
