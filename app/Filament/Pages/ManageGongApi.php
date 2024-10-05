<?php

namespace App\Filament\Pages;

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
                                Forms\Components\Fieldset::make()
                                    ->columns(1)
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_crm_data')
                                            ->label(__('Enable CRM Data'))
                                            ->helperText(__('Enable CRM data to be sent with calls. Requires the CRM to be setup in Gong.')),
                                    ]),
                                Forms\Components\Fieldset::make()
                                    ->columns(1)
                                    ->schema([
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
