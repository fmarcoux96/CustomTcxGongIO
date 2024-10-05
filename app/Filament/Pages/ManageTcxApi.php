<?php

namespace App\Filament\Pages;

use App\Services\TcxXapiClient;
use App\Settings\TcxApiSettings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class ManageTcxApi extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = TcxApiSettings::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label(__('Test Connection'))
                ->button()
                ->color(Color::Green)
                ->disabled(fn (TcxApiSettings $settings) => !$settings->valid())
                ->action(function (TcxApiSettings $settings, Component $livewire) {
                    $apiClient = new TcxXapiClient($settings);

                    try {
                        $settings->status = $apiClient->status();
                        $settings->connected = true;
                        $settings->save();

                        $version = $settings->status['Version'] ?? 'unknown';

                        Notification::make()
                            ->success()
                            ->title(__('Connection Test Successful'))
                            ->body("Version $version detected.")
                            ->send();

                        $livewire->dispatch('$refresh');
                    } catch (\Exception $e) {
                        report($e);

                        $settings->connected = false;
                        $settings->save();

                        Notification::make()
                            ->danger()
                            ->title(__('Connection Test Failed'))
                            ->body($e->getMessage())
                            ->send();
                    }
                })
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('General'))
                            ->schema([
                                Forms\Components\Fieldset::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('connected')
                                            ->label(__('Connection Status'))
                                            ->columnSpanFull()
                                            ->content(fn (TcxApiSettings $settings) => $settings->connected ? __('Connected') : __('Disconnected')),
                                        Forms\Components\TextInput::make('hostname')
                                            ->label(__('Hostname'))
                                            ->required()
                                            ->placeholder(__('Hostname')),
                                        Forms\Components\TextInput::make('port')
                                            ->label(__('Port'))
                                            ->numeric()
                                            ->default(443)
                                            ->required()
                                            ->placeholder(__('Port')),
                                        Forms\Components\TextInput::make('username')
                                            ->label(__('Username'))
                                            ->required()
                                            ->placeholder(__('Username')),
                                        Forms\Components\TextInput::make('password')
                                            ->label(__('Password'))
                                            ->required()
                                            ->placeholder(__('Password'))
                                            ->password()
                                            ->revealable()
                                    ]),
                                Forms\Components\Fieldset::make()
                                    ->columns(1)
                                    ->schema([
                                        Forms\Components\TextInput::make('api_key')
                                            ->label(__('API Key'))
                                            ->required()
                                            ->placeholder(__('API Key'))
                                    ])
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Status'))
                            ->schema([
                                Forms\Components\KeyValue::make('status')
                                    ->label(__('Status'))
                                    ->disabled(),
                            ]),
                    ])
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('3CX API');
    }

    public function getTitle(): string|Htmlable
    {
        return __('3CX API');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Integrations');
    }
}
