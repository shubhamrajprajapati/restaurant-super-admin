<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Services\SSHService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ManageRestaurantDB extends ManageRelatedRecords
{
    protected static string $resource = RestaurantResource::class;

    protected static string $relationship = 'db';

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $title = 'Manage Restaurant Database';

    protected static ?string $navigationLabel = 'Database Details';

    public static function getNavigationBadge(): ?string
    {
        // Get the ID of the currently open record
        $currentId = request()->route('record');

        // Count the related 'ftp' records for the current model instance
        $count = static::getResource()::getModel()::find($currentId)?->db()->count() ?? 0;

        // Return the count as a string or null if there are no related models
        return $count > 0 ? (string) $count : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('is_valid')
                    ->default(false)
                    ->label('Valid Credentials')
                    ->boolean()
                    ->inline()
                    ->grouped()
                    ->columnSpanFull()
                    ->hidden(false),
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->prefixIcon('heroicon-o-folder-open')
                    ->default('main database')
                    ->helperText('Used to identify the database details; for recognition purposes only.')
                    ->placeholder('e.g., main database, secondary database, etc')
                    ->required(),
                Forms\Components\Select::make('connection')
                    ->label('Database Driver')
                    ->native(false)
                    ->options([
                        'mysql' => 'MySQL',
                    ])
                    ->default('mysql')
                    ->helperText('Select the database driver to use. Currently, the only supported option is MySQL.')
                    ->placeholder('e.g., mysql'),
                Forms\Components\TextInput::make('host')
                    ->label('Database Host')
                    ->prefixIcon('heroicon-o-server')
                    ->required()
                    ->placeholder('e.g., localhost or 127.0.0.1')
                    ->helperText('Usually localhost; enter if different')
                    ->maxLength(255)
                    ->default('localhost'),
                Forms\Components\TextInput::make('database')
                    ->label('Database Name')
                    ->prefixIcon('heroicon-o-circle-stack')
                    ->required()
                    ->placeholder('e.g., your_database_name')
                    ->helperText('Enter the name of your database.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('Database Username')
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->required()
                    ->placeholder('e.g., root, db_username, etc')
                    ->helperText('Enter the database username.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Database Password')
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->nullable()
                    ->helperText('Enter the password associated with the Database username. Ensure it\'s secure and kept confidential.')
                    ->placeholder('Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('port')
                    ->label('Database Port')
                    ->prefixIcon('heroicon-o-key')
                    ->required()
                    ->helperText('Default is 3306; specify if different.')
                    ->numeric()
                    ->placeholder('e.g., 3306, custom_db_port, etc')
                    ->default(3306),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('host')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('connection')
                    ->label('Driver')
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('host')
                    ->label('Host')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('database')
                    ->label('Database')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->hiddenOn([SystemCheck::class]),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->hiddenOn([SystemCheck::class]),
                Tables\Columns\TextColumn::make('port')
                    ->label('Port')
                    ->sortable()
                    ->badge()
                    ->hiddenOn([SystemCheck::class]),
                Tables\Columns\TextColumn::make('password')
                    ->label('Password')
                    ->searchable()
                    ->icon('heroicon-o-document-duplicate')
                    ->iconPosition(IconPosition::After)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->hiddenOn([SystemCheck::class])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')
                    ->label('Default'),
                Tables\Columns\IconColumn::make('is_valid')
                    ->label('Is Valid'),
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
                    ->modalHeading('Add New Restaurant Database Details')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by_user_id'] = auth()->id();
                        $data['created_by_user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('make_default')
                    ->label('Set as Default')
                    ->requiresConfirmation()
                    ->visible(fn(Model $record) => !$record->is_valid || !$record->active)
                    ->modalHeading(function (Model $record) {
                        return "Make '{$record->host}' the Default Database Host";
                    })
                    ->modalDescription(function (Model $record) {
                        return new HtmlString("By confirming, you will set <strong>{$record->host}</strong> as the default database host. If the credentials are valid, all other database configurations for this restaurant will revert to non-default status.");
                    })
                    ->action(function (Action $action, $record): void {
                        try {
                            try {
                                $defaultSSH = $action->getRecord()->restaurant->ssh()->whereActive(true)->whereIsValid(true)->firstOrFail();
                                $sshConnected = new SSHService($defaultSSH);
                                
                                if ($sshConnected->ssh->isConnected() && $sshConnected->ssh->isAuthenticated()) {

                                    // Prepare MySQL command to check credentials
                                    $commandToCheckDatabaseCredentials = "{$record->connection} -u {$record->username} -p'{$record->password}' -h {$record->host} -e 'USE {$record->database};' 2>&1";

                                    // Execute command
                                    $output = $sshConnected->executeSimpleCommand($commandToCheckDatabaseCredentials);

                                    // Check output for success or failure
                                    if (strpos($output, 'ERROR') !== false) {
                                        Notification::make()
                                            ->danger()
                                            ->color('danger')
                                            ->title(__('Can\'t Set as Default: MySQL credentials are incorrect!'))
                                            ->body($output ?: 'An unexpected error occurred.')
                                            ->send();
                                        return;
                                    }

                                    $action->getRecord()->restaurant->db()->where('id', '!=', $action->getRecord()->id)->each(
                                        fn(Model $db) => $db->update([
                                            'active' => false,
                                        ])
                                    );

                                    $action->getRecord()->update([
                                        'active' => true,
                                        'is_valid' => true,
                                        'updated_by_user_id' => auth()->id(),
                                    ]);

                                    Notification::make()
                                        ->success()
                                        ->color('success')
                                        ->title(__('MySQL Authentication Successful!'))
                                        ->body(__('Congratulations! Database connected successfully and set as default.'))
                                        ->send();

                                    if ($action->getLivewire() instanceof SystemCheck) {
                                        $action->getLivewire()->dispatch('sshUpdated');
                                    }

                                }
                            } catch (\Exception $e) {
                                throw new \Exception(
                                    join('<br>', ['No valid default SSH credentials found! Please add default SSH credentials in the SSH Details tab.', '<br>Error Details given below:', $e->getMessage()])
                                );
                            }
                        } catch (\Exception $e) {
                            $action->getRecord()->update([
                                'active' => false,
                                'is_valid' => false,
                            ]);
                            Notification::make()
                                ->danger()
                                ->color('danger')
                                ->title(__('Can\'t Set as Default: SSH Authentication Failed!'))
                                ->body($e->getMessage() ?: 'An unexpected error occurred.')
                                ->send();
                            $action->cancel();
                        }
                    }),
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
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
