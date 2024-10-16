<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantResource\Pages;
use App\Models\Restaurant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                                Forms\Components\Toggle::make('status')
                                    ->required(),
                                Forms\Components\Toggle::make('online_order_status')
                                    ->required(),
                                Forms\Components\Toggle::make('reservation_status')
                                    ->required(),
                                Forms\Components\Toggle::make('shutdown_status')
                                    ->required(),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by_user_id'] = auth()->id();
                        $data['created_by_user_id'] = auth()->id();

                        return $data;
                    }),
            ])
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
