<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantSSHDetailsResource\Pages;
use App\Models\RestaurantSSHDetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RestaurantSSHDetailsResource extends Resource
{
    protected static ?string $model = RestaurantSSHDetails::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                Forms\Components\TextInput::make('ssh_host')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ssh_username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ssh_password')
                    ->password()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('ssh_private_key')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('ssh_port')
                    ->required()
                    ->numeric()
                    ->default(22),
                Forms\Components\Toggle::make('ssh_active')
                    ->required(),
                Forms\Components\TextInput::make('order_column')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('updated_by_user_id')
                    ->required()
                    ->maxLength(36),
                Forms\Components\TextInput::make('created_by_user_id')
                    ->required()
                    ->maxLength(36),
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
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ssh_host')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ssh_username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ssh_private_key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ssh_port')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('ssh_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order_column')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable(),
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
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurantSSHDetails::route('/'),
            'create' => Pages\CreateRestaurantSSHDetails::route('/create'),
            'edit' => Pages\EditRestaurantSSHDetails::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
