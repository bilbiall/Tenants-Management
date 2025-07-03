<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\InvoiceResource\Pages;
use App\Filament\Tenant\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Support\Facades\Auth;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;






class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //display data for a user/tenant
                TextColumn::make('invoice_number')->label('Invoice #'),
                TextColumn::make('amount')->money('KES')->label('Amount'),
                TextColumn::make('balance')->money('KES')->label('Balance'),
                TextColumn::make('status')->badge()->color(fn(string $state) => match ($state) {
                    'paid' => 'success',
                    'partial' => 'warning',
                    default => 'danger',
                }),
            TextColumn::make('invoice_date')->label('Invoice Date')->date(),
            TextColumn::make('due_date')->label('Due Date')->date(),
            ])
            ->filters([
                //tenants filters
                SelectFilter::make('status')
                    ->label('Payment Status')
                    ->options([
                        'Paid' => 'Paid',
                        'Partial' => 'Partial',
                        'Unpaid' => 'Unpaid',
                    ]),

                //filters for invoices month
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
            //'create' => Pages\CreateInvoice::route('/create'),
            //'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    //users to access their only invoices
    public static function getEloquentQuery(): Builder
    {
        $tenant = Auth::user()?->tenant;


        if (!$tenant) {
            return parent::getEloquentQuery()->whereRaw('1 = 0'); // prevent any access
        }

        return parent::getEloquentQuery()
            ->where('tenant_id', $tenant->id);
    }


}
