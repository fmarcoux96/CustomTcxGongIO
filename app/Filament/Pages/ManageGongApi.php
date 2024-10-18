<?php

namespace App\Filament\Pages;

use App\Services\GongApiClient;
use App\Settings\GongApiSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageGongApi extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GongApiSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('General'))
                            ->schema([
                                Forms\Components\Fieldset::make(__('Options'))
                                    ->columns(1)
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_crm_data')
                                            ->label(__('Enable CRM Data'))
                                            ->helperText(__('Enable CRM data to be sent with calls. Requires the CRM to be setup in Gong.')),
                                        Forms\Components\Select::make('fallback_user_id')
                                            ->label(__('Fallback User'))
                                            ->helperText(__('If the user is not found in the CRM, use this user ID. If empty, calls without agent won\'t be uploaded.'))
                                            ->disabled(fn (GongApiSettings $settings) => !$settings->valid())
                                            ->native(false)
                                            ->selectablePlaceholder()
                                            ->options(function (Forms\Get $get, GongApiSettings $settings) {
                                                if (!$settings->valid()) {
                                                    return [];
                                                }

                                                $api = new GongApiClient($settings);
                                                return $api->getUsers();
                                            }),
                                    ]),
                                Forms\Components\Fieldset::make(__('Credentials'))
                                    ->columns(1)
                                    ->schema([
                                        Forms\Components\Select::make('auth_type')
                                            ->label(__('Authentication Type'))
                                            ->native(false)
                                            ->options([
                                                'basic' => 'Basic',
                                                'oauth2' => 'OAuth2',
                                            ])
                                            ->default('basic')
                                            ->disableOptionWhen(fn ($value) => $value === 'oauth2')
                                            ->selectablePlaceholder(false)
                                            ->required(),
                                        Forms\Components\TextInput::make('api_base_url')
                                            ->label(__('API Base URL'))
                                            ->required()
                                            ->placeholder('https://')
                                            ->url(),
                                        Forms\Components\TextInput::make('access_key')
                                            ->label(__('Access Key'))
                                            ->required()
                                            ->placeholder(__('Access Key'))
                                            ->password()
                                            ->revealable(),
                                        Forms\Components\TextInput::make('access_secret')
                                            ->label(__('Access Secret'))
                                            ->required()
                                            ->placeholder(__('Access Secret'))
                                            ->password()
                                            ->revealable()
                                    ]),
                            ])
                    ])
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('Gong API');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Gong API');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Integrations');
    }
}
