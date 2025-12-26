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
