<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageRestaurantFTP extends ManageRelatedRecords
{
    protected static string $resource = RestaurantResource::class;

    protected static string $relationship = 'ftp';

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $title = 'Manage Restaurant FTP';

    protected static ?string $navigationLabel = 'FTP Details';

    public static function getNavigationBadge(): ?string
    {
        // Get the ID of the currently open record
        $currentId = request()->route('record');

        // Count the related 'ftp' records for the current model instance
        $count = static::getResource()::getModel()::find($currentId)?->ftp()->count() ?? 0;

        // Return the count as a string or null if there are no related models
        return $count > 0 ? (string) $count : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('active')
                    ->default(false)
                    ->label('Active')
                    ->boolean()
                    ->inline()
                    ->grouped()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('server')
                    ->label('FTP Server')
                    ->prefixIcon('heroicon-o-server')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('FTP Username')
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('FTP Password')
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->required()
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('port')
                    ->label('FTP Port')
                    ->prefixIcon('heroicon-o-key')
                    ->required()
                    ->numeric()
                    ->default(21),
                Forms\Components\TextInput::make('directory')
                    ->label('FTP Directory')
                    ->prefixIcon('heroicon-o-folder')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('server')
            ->columns([
                Tables\Columns\TextColumn::make('server')
                    ->label('Server')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('port')
                    ->label('Port')
                    ->badge()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('directory')
                    ->label('Directory')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Active')
                    ->onIcon('heroicon-o-eye')
                    ->onColor('success')
                    ->offIcon('heroicon-o-eye-slash')
                    ->offColor('danger'),
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
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    $data['updated_by_user_id'] = auth()->id();
                    $data['created_by_user_id'] = auth()->id();

                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by_user_id'] = auth()->id();

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
