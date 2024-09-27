<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageRestaurantSSH extends ManageRelatedRecords
{
    protected static string $resource = RestaurantResource::class;

    protected static string $relationship = 'ssh';

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $title = "Manage Restaurant SSH";

    protected static ?string $navigationLabel = 'SSH Details';

    public static function getNavigationBadge(): string|null
    {
        // Get the ID of the currently open record
        $currentId = request()->route('record');

        // Count the related 'ftp' records for the current model instance
        $count = static::getResource()::getModel()::find($currentId)?->ssh()->count() ?? 0;

        // Return the count as a string or null if there are no related models
        return $count > 0 ? (string) $count : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('ssh_active')
                    ->default(false)
                    ->label('Active')
                    ->boolean()
                    ->inline()
                    ->grouped()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ssh_host')
                    ->label('SSH Host')
                    ->prefixIcon('heroicon-o-server')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ssh_username')
                    ->label('SSH Username')
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ssh_password')
                    ->label('SSH Password')
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->required()
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                Forms\Components\Textarea::make('ssh_private_key')
                    ->label('SSH Private Key')
                    ->maxLength(255)
                    ->nullable()
                    ->autosize()
                    ->hidden(),
                Forms\Components\TextInput::make('ssh_port')
                    ->label('SSH Port')
                    ->prefixIcon('heroicon-o-key')
                    ->required()
                    ->numeric()
                    ->default(22),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ssh_host')
            ->columns([
                Tables\Columns\TextColumn::make('ssh_host')
                    ->label('Host')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('ssh_username')
                    ->label('Username')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('ssh_private_key')
                    ->label('Private Key')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ssh_port')
                    ->label('Port')
                    ->sortable()
                    ->badge()
                    ->numeric(),
                Tables\Columns\ToggleColumn::make('ssh_active')
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
                Tables\Filters\TrashedFilter::make()
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
