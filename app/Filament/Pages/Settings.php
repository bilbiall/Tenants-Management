<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $slug = 'settings';
    protected static ?string $navigationGroup = 'My Records';
    protected static ?string $title = 'System Settings';

    protected static string $view = 'filament.pages.settings';

    /**
     * Holds form state
     */
    public ?array $data = [];

    /**
     * Load settings into the form
     */
    public function mount(): void
    {
        $settings = Setting::singleton();

        // Fill the form with decoded payload (or empty array if null)
        $this->form->fill($settings->payload ?? []);
    }


    /**
     * Define the form schema
     */
    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('SMS')
                            ->schema([
                                Forms\Components\TextInput::make('sms_url')
                                    ->label('SMS API URL')
                                    ->url()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('sms_api_key')
                                    ->label('SMS API Key')
                                    ->password()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('sms_partner_id')
                                    ->label('SMS Partner ID')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('sms_sender_id')
                                    ->label('SMS Sender ID')
                                    ->maxLength(50),
                            ]),

                        Forms\Components\Tabs\Tab::make('Templates')
                            ->schema([
                                Forms\Components\Textarea::make('template_invoice')
                                    ->label('Invoice Notification Template')
                                    ->helperText('Variables: {tenant_name}, {invoice_number}, {amount}, {due_date}')
                                    ->rows(4),

                                    Forms\Components\Textarea::make('template_payment')
                                        ->label('Payment Confirmation Template')
                                        ->helperText('Variables: {tenant_name}, {amount_paid}, {invoice_number}, {balance}, {app_name}')
                                        ->default('Hi {tenant_name}, we\'ve received your payment of KES {amount_paid} for Invoice #{invoice_number}. Your remaining balance is KES {balance}. Thank you. - {app_name}')
                                        ->rows(4),

                                Forms\Components\Textarea::make('template_payment_reminder')
                                    ->label('Payment Reminder Template')
                                    ->helperText('Variables: {tenant_name}, {amount}, {due_date}')
                                    ->rows(4),

                                Forms\Components\Textarea::make('template_issue_notification')
                                    ->label('Issue Notification Template')
                                    ->helperText('Variables: {tenant_name}, {issue_title}, {issue_description}')
                                    ->rows(4),

                                Forms\Components\Textarea::make('template_tenant_welcome')
                                    ->label('Tenant Welcome Template')
                                    ->helperText('Variables: {tenant_name}, {app_name}, {house_name}, {rent_amount}')
                                    ->default('Hello {tenant_name}, welcome to {app_name}. You were admitted to {house_name} with a monthly rent of KES {rent_amount}')
                                    ->rows(4),
                            ]),

                        Forms\Components\Tabs\Tab::make('Email')
                            ->schema([
                                // Future SMTP settings
                            ]),

                        Forms\Components\Tabs\Tab::make('Payments')
                            ->schema([
                                // Future payment gateway settings (M-Pesa, etc.)
                            ]),
                    ]),
            ]);
    }

    /**
     * Persist settings
     */
    public function save(): void
    {
        $settings = Setting::singleton();

        // Store all form data inside the 'payload' JSON column
        $settings->payload = $this->form->getState();
        $settings->save();

        cache()->forget('settings_singleton');

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

}
