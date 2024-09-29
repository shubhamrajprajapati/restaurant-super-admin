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
        // Extensions to check
        $extensionsToCheck = [
            "BCMath", "Ctype", "cURL", "DOM", "Fileinfo", "JSON",
            "Mbstring", "OpenSSL", "PCRE", "PDO", "Tokenizer", "XML"
        ];
        preg_match("#^\d.\d#", PHP_VERSION, $match);
        // echo $match[0]; 
        $systemCheck = json_decode(shell_exec('C:\Users\hp\shubham.bat'));
        // Check if each extension is installed
        foreach ($extensionsToCheck as $ext) {
            // Convert to lowercase for case-insensitive comparison
            $extLower = strtolower($ext);

            if (in_array($extLower, $systemCheck->modules)) {
                // echo "$ext is installed.\n";
            } else {
                // echo "$ext is NOT installed.\n";
            }
        }
        // dd($systemCheck);
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
                Forms\Components\ToggleButtons::make('active')
                    ->default(false)
                    ->label('Active')
                    ->boolean()
                    ->inline()
                    ->grouped()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('SSH Name')
                    ->prefixIcon('heroicon-o-folder-open')
                    ->default('public')
                    ->helperText('This name is used to identify the directory where the SSH credentials will be applied or where root access is granted. It serves solely for recognition purposes.')
                    ->placeholder('e.g., public, public_html, etc')
                    ->required(),
                Forms\Components\Textarea::make('default_cmd')
                    ->label('SSH Default Command')
                    ->helperText('This command will execute first when these credentials are used for connection.')
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('e.g., cd www && ls -la')
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
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('private_key')
                    ->label('Private Key')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('default_cmd')
                    ->label('Default Command')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('port')
                    ->label('Port')
                    ->sortable()
                    ->badge()
                    ->numeric(),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Active')
                    ->onIcon('heroicon-o-eye')
                    ->onColor('success')
                    ->offIcon('heroicon-o-eye-slash')
                    ->offColor('danger'),
                Tables\Columns\IconColumn::make('active')
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
