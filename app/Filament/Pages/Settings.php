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
                                // Pesapal Settings
                                Forms\Components\Section::make('Pesapal')
                                    ->schema([
                                        Forms\Components\TextInput::make('pesapal.consumer_key')
                                            ->label('Pesapal Consumer Key')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('pesapal.consumer_secret')
                                            ->label('Pesapal Consumer Secret')
                                            ->password()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('pesapal.webhook_secret')
                                            ->label('Pesapal Webhook Secret')
                                            ->password()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('pesapal.ipn_id')
                                            ->label('Pesapal IPN ID')
                                            ->helperText('Register via Pesapal API: POST /api/3/notification-urls. Sandbox and Live have separate IPN IDs.')
                                            ->placeholder('e.g., a12b34cd-5678-90ef-aaaa-bbbbccccdddd')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('pesapal.callback_url')
                                            ->label('Pesapal Callback URL')
                                            ->helperText('Public webhook/callback URL Pesapal will call (e.g., https://example.com/api/pesapal/webhook)')
                                            ->url()
                                            ->maxLength(1024),

                                        Forms\Components\Toggle::make('pesapal.sandbox')
                                            ->label('Use Pesapal Sandbox')
                                            ->default(true),

                                        Forms\Components\TextInput::make('pesapal.currency')
                                            ->label('Currency')
                                            ->default('KES')
                                            ->maxLength(10),
                                    ]),

                                // M-Pesa Daraja Settings
                                Forms\Components\Section::make('M-Pesa (Daraja API)')
                                    ->schema([
                                        Forms\Components\TextInput::make('mpesa.consumer_key')
                                            ->label('Daraja API Key')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('mpesa.consumer_secret')
                                            ->label('Daraja API Secret')
                                            ->password()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('mpesa.business_shortcode')
                                            ->label('Business Short Code')
                                            ->placeholder('e.g., 174379')
                                            ->maxLength(10),

                                        Forms\Components\TextInput::make('mpesa.passkey')
                                            ->label('M-Pesa Online Passkey')
                                            ->password()
                                            ->helperText('From your M-Pesa merchant dashboard')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('mpesa.callback_url')
                                            ->label('M-Pesa Callback URL')
                                            ->helperText('Public webhook/callback URL Safaricom will call (e.g., https://example.com/api/mpesa/callback)')
                                            ->url()
                                            ->maxLength(1024),

                                        Forms\Components\Toggle::make('mpesa.sandbox')
                                            ->label('Use Sandbox (Daraja Test)')
                                            ->default(true),

                                        Forms\Components\TextInput::make('mpesa.currency')
                                            ->label('Currency')
                                            ->default('KES')
                                            ->maxLength(10),
                                    ]),
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
