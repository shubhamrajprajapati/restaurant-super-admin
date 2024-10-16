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
use Livewire\Component;

class ManageRestaurantSSH extends ManageRelatedRecords
{
    protected static string $resource = RestaurantResource::class;

    protected static string $relationship = 'ssh';

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $title = 'Manage Restaurant SSH';

    protected static ?string $navigationLabel = 'SSH Details';

    public static function getNavigationBadge(): ?string
    {
        // Extensions to check
        // $extensionsToCheck = [
        //     "BCMath", "Ctype", "cURL", "DOM", "Fileinfo", "JSON",
        //     "Mbstring", "OpenSSL", "PCRE", "PDO", "Tokenizer", "XML"
        // ];
        // preg_match("#^\d.\d#", PHP_VERSION, $match);
        // echo $match[0];
        // $systemCheck = json_decode(shell_exec('C:\Users\hp\shubham.bat'));
        // Check if each extension is installed
        // foreach ($extensionsToCheck as $ext) {
        //     // Convert to lowercase for case-insensitive comparison
        //     $extLower = strtolower($ext);

        //     if (in_array($extLower, $systemCheck->modules)) {
        //         // echo "$ext is installed.\n";
        //     } else {
        //         // echo "$ext is NOT installed.\n";
        //     }
        // }
        // dd($systemCheck);
        // Get the ID of the currently open record
        $currentId = request()->route('record');

        // Count the related 'ftp' records for the current model instance
        $count = static::getResource()::getModel()::find($currentId)?->ssh()->count() ?? 0;

        // Return the count as a string or null if there are no related models
        return $count > 0 ? (string) $count : null;
    }

    // Define the hidden check function
    public function hiddenCheck($livewire)
    {
        return in_array($livewire::class, [SystemCheck::class], true);
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
                    ->label('SSH Name')
                    ->prefixIcon('heroicon-o-folder-open')
                    ->default('public')
                    ->helperText('This name is used to identify the directory where the SSH credentials will be applied or where root access is granted. It serves solely for recognition purposes.')
                    ->placeholder('e.g., public, public_html, etc')
                    ->required(),
                Forms\Components\Textarea::make('default_cmd')
                    ->label('Custom SSH Command')
                    ->helperText(
                        new HtmlString('
                        <p class="text-xs leading-3">
                        Enter the command to navigate to the installation directory. For example:<br>
                            <code class="font-semibold">cd htdocs && cd public</code><br>
                            <code class="font-semibold">cd www</code><br>
                            This command will be executed before the installation process.
                        </p>
                        ')
                    )
                    ->hint('Learn more')
                    ->hintIcon('heroicon-o-question-mark-circle')
                    ->hintIconTooltip('Enter the command to navigate to the installation directory. For example: cd htdocs && cd public, cd www. This command will be executed before the installation process. You can modify it multiple times if needed.')
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('e.g., cd htdocs && cd public, cd www')
                    ->autosize(),
                Forms\Components\TextInput::make('host')
                    ->label('SSH Host')
                    ->prefixIcon('heroicon-o-server')
                    ->required()
                    ->placeholder('e.g., 127.0.0.1, example.com')
                    ->helperText('Enter the IP address or domain URL that resolves to the same IP.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('SSH Username')
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->required()
                    ->placeholder('e.g., user123')
                    ->helperText('Enter the username you use to connect to the server via SSH. This is typically the account name on the server.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('SSH Password')
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->required()
                    ->helperText('Enter the password associated with the SSH username. Ensure it\'s secure and kept confidential.')
                    ->placeholder('Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                Forms\Components\Textarea::make('private_key')
                    ->label('SSH Private Key')
                    ->maxLength(255)
                    ->nullable()
                    ->autosize()
                    ->hidden(),
                Forms\Components\TextInput::make('port')
                    ->label('SSH Port')
                    ->prefixIcon('heroicon-o-key')
                    ->required()
                    ->helperText('Enter the port number used for SSH connections. The default port is 22, but it may be different if the server is configured to use a custom port.')
                    ->numeric()
                    ->placeholder('e.g., 22')
                    ->default(22),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->emptyStateHeading('No SSH Details Found')
            ->emptyStateDescription('To get started, create the first restaurant SSH detail.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->label('Host')
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
                    ->copyMessageDuration(1500)
                    ->hiddenOn([SystemCheck::class]),
                Tables\Columns\TextColumn::make('private_key')
                    ->label('Private Key')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->hiddenOn([SystemCheck::class]),
                Tables\Columns\TextColumn::make('default_cmd')
                    ->label('Custom SSH Command')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('port')
                    ->label('Port')
                    ->sortable()
                    ->badge()
                    ->hiddenOn([SystemCheck::class]),
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
                    ->modalHeading('Create New Restaurant SSH Details')
                    ->label('New Restaurant SSH Details')
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire))
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
                        return "Set '$record->host' as Default";
                    })
                    ->modalDescription(function (Model $record) {
                        return "If the provided credentials are correct, this will set '$record->host' as the default. All other SSH configurations for this restaurant will be reverted to non-default status.";
                    })
                    ->action(function (Action $action): void {
                        try {
                            $sshConnected = new SSHService($action->getRecord());
                            if ($sshConnected->ssh->isConnected() && $sshConnected->ssh->isAuthenticated()) {
                                $action->getRecord()->restaurant->ssh()->where('id', '!=', $action->getRecord()->id)->each(
                                    fn(Model $ssh) => $ssh->update([
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
                                    ->title(__('SSH Authentication Successful!'))
                                    ->body(__('Congrats! SSH connected successfully and set as default.'))
                                    ->send();

                                if ($action->getLivewire() instanceof SystemCheck) {
                                    $action->getLivewire()->dispatch('sshUpdated');
                                }
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
                Tables\Actions\ViewAction::make()
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by_user_id'] = auth()->id();

                        return $data;
                    })
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                Tables\Actions\ForceDeleteAction::make()
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                Tables\Actions\RestoreAction::make()
                    ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                    Tables\Actions\RestoreBulkAction::make()
                        ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->hidden(fn(Component $livewire) => $this->hiddenCheck($livewire)),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
            );
    }
}
