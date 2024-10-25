<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $recordTitleAttribute = 'name';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static ?string $navigationGroup = 'Restaurants';

    protected static ?string $navigationBadgeTooltip = ' Restaurants';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::$model::count() . static::$navigationBadgeTooltip;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Details')
                            ->columnSpanFull()
                            ->columns(12)
                            ->schema([
                                Forms\Components\Hidden::make('installation_token')
                                    ->default(Str::random(40)),
                                Forms\Components\Fieldset::make('Restaurant Logo')
                                    ->columnSpan(['lg' => 3])
                                    ->schema([
                                        Forms\Components\FileUpload::make('logo')
                                            ->extraAttributes(['class' => 'mx-auto'])
                                            ->disk('public')
                                            ->directory('restaurant-logos')
                                            ->avatar()
                                            ->image()
                                            ->hiddenLabel()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->required()
                                            ->maxSize(1024)
                                            ->downloadable()
                                            ->openable()
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Fieldset::make('Restaurant Name & Domain Url')
                                    ->columns(['lg' => 12])
                                    ->columnSpan(['lg' => 9])
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->hiddenLabel()
                                            ->placeholder('Restaurant Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('domain')
                                            ->hiddenLabel()
                                            ->placeholder('Restaurant Domain Url')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->autosize()
                                    ->columnSpanFull(),
                                Forms\Components\Fieldset::make('Restaurant App Installation Status')
                                    ->columns(['lg' => 3])
                                    ->columnSpan(['lg' => 12])
                                    ->schema([
                                        Forms\Components\Toggle::make('featured')
                                            ->hidden()
                                            ->required(),
                                        Forms\Components\Toggle::make('visible')
                                            ->hidden()
                                            ->required(),
                                        Forms\Components\Toggle::make('verified')
                                            ->default(false)
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->onIcon('heroicon-o-arrow-down-tray')
                                            ->offIcon('heroicon-o-no-symbol')
                                            ->label('Installed')
                                            ->helperText('Toggle to indicate if the Restaurant App is installed. If the installation is incomplete, switch off to enable the installation button in the system check. This option is intended for debugging and processing manual installations.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Settings')
                            ->schema([
                                Forms\Components\Section::make('Close Restaurant')
                                    ->description('Manage the status and message for temporarily closing your restaurant.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->label(fn(Get $get): ?string => $get('status') ? 'Closed Now' : 'Open Now')
                                            ->helperText(
                                                fn(Get $get): ?string => $get('status')
                                                ? 'The service is currently marked as closed. You can also add a custom closing message.'
                                                : 'The restaurant is currently open. You can click the toggle button to mark it as closed.')
                                            ->onIcon('heroicon-o-lock-closed')
                                            ->onColor('danger')
                                            ->offIcon('heroicon-o-lock-open')
                                            ->offColor('success')
                                            ->live(),
                                        Forms\Components\RichEditor::make('status_msg')
                                            ->label('Closure Message')
                                            ->placeholder('Type a message for customers regarding the closure.')
                                            ->visible(fn(Get $get): bool => $get('status')),
                                    ]),
                                Forms\Components\Section::make('Online Order')
                                    ->description('Set the availability of online orders and communicate any changes.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('online_order_status')
                                            ->label(
                                                fn(Get $get): string => $get('online_order_status') ? 'Open for Online Orders' : 'Closed for Online Orders'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('online_order_status')
                                                ? 'The restaurant is open for online orders. Click the toggle button to mark it as closed.'
                                                : 'The restaurant is currently closed for online orders. Toggle on to allow orders again. You can also add a custom closing message.'
                                            )
                                            ->onIcon('heroicon-o-shopping-cart')
                                            ->onColor('success')
                                            ->offIcon('heroicon-o-shopping-cart')
                                            ->offColor('danger')
                                            ->live(),
                                        Forms\Components\RichEditor::make('online_order_msg')
                                            ->label('Order Message')
                                            ->placeholder('Type a message for customers regarding closing online orders.')
                                            ->hidden(fn(Get $get): bool => $get('online_order_status')),
                                    ]),
                                Forms\Components\Section::make('Reservation')
                                    ->description('Control the reservation status and provide necessary information.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('reservation_status')
                                            ->label(
                                                fn(Get $get): string => $get('reservation_status') ? 'Open for Reservations' : 'Closed for Reservations'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('reservation_status')
                                                ? 'The restaurant is open for reservations. Click the toggle button to mark it as closed for new reservations.'
                                                : 'The restaurant is currently closed for reservations. Toggle off to allow reservations again. You can also add a custom closing message.'
                                            )
                                            ->onIcon('heroicon-o-calendar-days')
                                            ->onColor('success')
                                            ->offIcon('heroicon-o-calendar-days')
                                            ->offColor('danger')
                                            ->live(),
                                        Forms\Components\RichEditor::make('reservation_msg')
                                            ->label('Reservation Closing Message')
                                            ->placeholder('Type a message for customers regarding closing reservations.')
                                            ->hidden(fn(Get $get): bool => $get('reservation_status')),
                                    ]),
                                Forms\Components\Section::make('Shutdown')
                                    ->description('Manage the restaurant shutdown process and communicate with customers.')
                                    ->compact()
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Toggle::make('shutdown_status')
                                            ->label(
                                                fn(Get $get): string => $get('shutdown_status') ? 'Shutdown' : 'Operational'
                                            )
                                            ->helperText(
                                                fn(Get $get): string => $get('shutdown_status')
                                                ? 'The restaurant is currently shut down. Toggle off to resume operations. You can also add a custom shutdown message.'
                                                : 'The restaurant is operational. Click the toggle button to mark it as shut down.'
                                            )
                                            ->onIcon('heroicon-o-power')
                                            ->onColor('danger')
                                            ->offIcon('heroicon-o-check')
                                            ->offColor('success')
                                            ->live(),
                                        Forms\Components\RichEditor::make('shutdown_msg')
                                            ->label('Shutdown Message')
                                            ->placeholder('Type a message for customers regarding the shutdown.')
                                            ->hidden(fn(Get $get): bool => !$get('shutdown_status')),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Other Details')
                            ->schema([
                                Forms\Components\Repeater::make('other_details')
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->columns(12)
                                    ->collapsible()
                                    ->reorderableWithButtons()
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->columnSpan(['lg' => 6]),
                                        Forms\Components\TextInput::make('value')
                                            ->columnSpan(['lg' => 6]),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString(),

                Forms\Components\Fieldset::make('Contribution Log')
                    ->hiddenOn('create')
                    ->columns(['lg' => 4])
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Placeholder::make('Updated By')
                            ->content(fn(Model $record): ?string => $record?->updater?->name),
                        Forms\Components\Placeholder::make('Updated At')
                            ->content(fn(Model $record): ?string => $record?->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('Created By')
                            ->content(fn(Model $record): string => $record?->creator?->name),
                        Forms\Components\Placeholder::make('Created At')
                            ->content(fn(Model $record): string => $record?->created_at?->toFormattedDateString()),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order_column')
            ->paginatedWhileReordering()
            ->defaultSort('order_column')
            ->deferLoading()
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\IconColumn::make('featured')
                    ->hidden()
                    ->boolean(),
                Tables\Columns\IconColumn::make('visible')
                    ->hidden()
                    ->boolean(),
                Tables\Columns\IconColumn::make('verified')
                    ->label('Is Installed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Close Restaurant')
                    ->boolean(),
                Tables\Columns\IconColumn::make('online_order_status')
                    ->label('Online Order')
                    ->boolean(),
                Tables\Columns\IconColumn::make('reservation_status')
                    ->label('Reservation')
                    ->boolean(),
                Tables\Columns\IconColumn::make('shutdown_status')
                    ->label('Shutdown')
                    ->boolean(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updater.name')
                    ->label('Updated By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make()->mutateFormDataUsing(function (array $data): array {
                    $data['updated_by_user_id'] = auth()->id();

                    return $data;
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
            'ssh' => Pages\ManageRestaurantSSH::route('/{record}/ssh'),
            'db' => Pages\ManageRestaurantDB::route('/{record}/db'),
            'ftp' => Pages\ManageRestaurantFTP::route('/{record}/ftp'),
            'system-check' => Pages\SystemCheck::route('/{record}/system-check'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditRestaurant::class,
            Pages\ManageRestaurantDB::class,
            Pages\ManageRestaurantSSH::class,
            // Pages\ManageRestaurantFTP::class,
            Pages\SystemCheck::class,
        ]);
    }
}
