<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Models\Restaurant;
use App\Models\RestaurantSSHDetails;
use App\Services\SSHService;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class SystemCheck extends Page implements HasForms, HasInfolists, HasTable
{
    use InteractsWithForms, InteractsWithInfolists, InteractsWithRecord, InteractsWithTable;

    protected static string $resource = RestaurantResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.resources.restaurant-resource.pages.system-check-page';

    public ?array $data = [];

    public int $sshCount = 0;

    public RestaurantSSHDetails $defaultSSH;

    public array $serverInfo = []; // Declare as an object type, nullable

    public ?string $pwd;

    public function mount(int | string $record)
    {
        $this->record = $this->resolveRecord($record);
        $this->sshCount = $this->record?->ssh?->count();
        $this->getDefaultSSH();
    }

    private function getInstallationDirCommand()
    {
        return $this->defaultSSH?->default_cmd ?? '';
    }

    private function connectSSH()
    {
        return new SSHService($this->defaultSSH);
    }

    private function execSimpleSSH($command, $realtime = false)
    {
        try {
            $sshConnected = $this->connectSSH();

            if (!$sshConnected->isConnected()) {
                return (object) [
                    'status' => false,
                    'title' => 'Error Occurred!',
                    'body' => '<div class=\'overflow-x-auto\'><pre>SSH connection not established.</pre><br><div>',
                    'plain_body' => 'SSH connection not established.',
                ];
            }

            // Execute the command
            $output = $sshConnected->executeSimpleCommand($command, $realtime);

            return (object) [
                'status' => true,
                'title' => "Command Executed Successfully: <strong class='text-primary-600 dark:text-primary-400'>$command<strong>",
                'body' => "<div class=\"overflow-x-auto\"><pre>{$output}</pre><br></div>",
                'plain_body' => $output,
            ];
        } catch (\Exception $e) {
            $error = (object) [
                'status' => false,
                'title' => 'Error: SSH Connection Failed.',
                'body' => "<div class='overflow-x-auto'><pre>{$e->getMessage()}</pre><br></div>",
                'plain_body' => $e->getMessage(),
            ];
            $this->sendNotification($error);
            return $error;
        }
    }

    private function sendNotification(\stdClass $data = null): void
    {
        Notification::make()
            ->title($data?->title)
            ->body($data?->body)
            ->status(fn() => $data?->type ?? ($data?->status ? 'success' : 'danger'))
            ->icon(fn() => isset($data?->icon) ? ($data?->icon ?: null): null)
            ->send();
    }

    #[On('sshUpdated')]
    public function getDefaultSSH()
    {
        $this->defaultSSH = $this->record->ssh()
            ->whereActive(true)
            ->whereIsValid(true)
            ->first() ?? new RestaurantSSHDetails;

        if ($this->defaultSSH?->id) {
            $command = file_get_contents(base_path('installation/system-check-inline.sh'));
            $result = $this->execSimpleSSH($command);

            // Check result status
            $this->serverInfo = $result?->status ? (json_decode($result->plain_body, true) ?? []) : [];
            if (count($this->serverInfo)) {
                $this->arrangeDefaultSSH();
                $this->runInstallationDirectoryCmd(showNotification: false);
                $pwdOutput = $this->runInstallationDirectoryCmd(append: 'pwd', showNotification: false, return :true);
                $this->pwd = $pwdOutput->plain_body;
            }
        }
    }

    protected function arrangeDefaultSSH()
    {
        // This method is not used in the code, but it's here to show how to arrange
        $this->setCommonPropertiesForDefaultSSH('apache_version', 'apache.webp');
        $this->setCommonPropertiesForDefaultSSH('nginx_version', 'apache.webp');
        $this->setCommonPropertiesForDefaultSSH('git_version', 'apache.webp');
        $this->setCommonPropertiesForDefaultSSH('mysql_version', 'apache.webp');
        $this->setCommonPropertiesForDefaultSSH('composer_version', 'apache.webp');
        $this->setCommonPropertiesForDefaultSSH('php_version', 'apache.webp');
    }

    protected function setCommonPropertiesForDefaultSSH($key, $logo)
    {
        $key = str_replace('_version', '', $key);
        $this->serverInfo[$key] = [];
        $this->serverInfo[$key]['logo'] = asset("assets/img/server-logos/$key.webp");
        if (isset($this->serverInfo["{$key}_version"])) {
            $this->serverInfo[$key]['version'] = $this->serverInfo["{$key}_version"];
            $this->serverInfo[$key]['description'] = 'Installed';
            $this->serverInfo[$key]['extraAttributes'] = ['style' => 'background-color:rgb(51, 170, 51, .1);'];
            $this->serverInfo[$key]['icon'] = 'heroicon-o-check-circle';
            $this->serverInfo[$key]['color'] = 'success';
        } else {
            $this->serverInfo[$key]['version'] = 'Not Found';
            $this->serverInfo[$key]['description'] = 'Not Installed';
            $this->serverInfo[$key]['extraAttributes'] = ['style' => 'background-color:rgb(255, 0, 0, .1);'];
            $this->serverInfo[$key]['icon'] = 'heroicon-o-x-mark';
            $this->serverInfo[$key]['color'] = 'danger';
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->id('remote-server-cmd-run-form')
                    ->statePath('data')
                    ->heading('Execute Commands')
                    ->description('You can now run commands directly on this remote server within the installation directory.')
                    ->icon('heroicon-o-command-line')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->headerActions([
                        Forms\Components\Actions\Action::make('Run command')
                            ->requiresConfirmation()
                            ->modalHeading('Confirmation Required')
                            ->modalDescription('Please confirm before executing any commands on the remote server. Improper use may result in data loss if you are not fully aware of the commands you intend to run.')
                            ->color('danger')
                            ->icon('heroicon-o-bolt')
                            ->extraAttributes(['type' => 'submit'])
                            ->action(function (): void {
                                $data = (object) $this->form->getState()['data'];
                                $this->runSshOnRemoteServer($data->command);
                            }),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('command')
                            ->hiddenLabel()
                            ->placeholder('Enter command like- ls, cd www, etc')
                            ->required()
                            ->hint(fn() => new HtmlString("<small><code>$this->pwd</code></small>"))
                            ->hintColor('danger')
                            ->hintIcon('heroicon-o-question-mark-circle')
                            ->hintIconTooltip('This is the path where your custom command will be executed.')
                            ->suffixActions(
                                [
                                    Forms\Components\Actions\Action::make('Run command')
                                        ->requiresConfirmation()
                                        ->modalHeading('Confirmation Required')
                                        ->modalDescription('Please confirm before executing any commands on the remote server. Improper use may result in data loss if you are not fully aware of the commands you intend to run.')
                                        ->color('danger')
                                        ->icon('heroicon-o-bolt')
                                        ->extraAttributes(['type' => 'submit'])
                                        ->action(function (): void {
                                            $data = (object) $this->form->getState()['data'];
                                            $this->runSshOnRemoteServer($data->command);
                                        }),
                                ]
                            ),

                        Forms\Components\Placeholder::make('output')
                            ->hiddenLabel()
                            ->hintIcon('heroicon-o-command-line')
                            ->hintColor('danger')
                            ->extraAttributes(['class' => 'overflow-x-auto'])
                            ->visible(isset($this->serverInfo['custom_cmd_output']))
                            ->content(
                                new HtmlString("<div class=\"overflow-x-auto\"><pre>" . ($this->serverInfo['custom_cmd_output'] ?? '') . "</pre></div>")
                            ),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $resSSH = new ManageRestaurantSSH;

        return $resSSH->table($table)
            ->relationship(fn(): HasMany => $this->record->ssh())
            ->inverseRelationship('restaurant')
            ->defaultSort('is_valid', 'desc')
            ->deferLoading()
            ->heading(function () {
                if ($this->sshCount) {
                    return 'Set Default SSH Credentials';
                } else {
                    return 'No SSH configurations found.';
                }
            })
            ->description(function () {
                if ($this->sshCount) {
                    return 'It appears you\'ve added SSH credentials, but they haven\'t been set as default. Please click the \'Make Default\' button to set them as the default configuration.';
                } else {
                    return 'Please create a new SSH configuration.';
                }
            })
            ->emptyStateHeading(fn(Table $table) => $table->getHeading())->emptyStateDescription(fn(Table $table) => $table->getDescription())
            ->emptyStateActions([
                TableAction::make('create')
                    ->label('Create New')
                    ->icon('heroicon-m-plus')
                    ->labeledFrom('md')
                    ->url(function () {
                        return ManageRestaurantSSH::getUrl(parameters: [
                            'tableAction' => CreateAction::getDefaultName(),
                            'record' => $this->record,
                        ]);
                    }),
            ]);
    }

    public function defaultSSHInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->defaultSSH)
            ->schema([
                Infolists\Components\Section::make('Default SSH')
                    ->description('Using the default SSH settings to check the system. You can modify these settings in the SSH Details section.')
                    ->icon('heroicon-o-command-line')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->headerActions([
                        Action::make('edit')
                            ->label('Edit')
                            ->icon('heroicon-m-pencil-square')
                            ->url(function (RestaurantSSHDetails $record) {
                                return ManageRestaurantSSH::getUrl(parameters: [
                                    'tableAction' => EditAction::getDefaultName(),
                                    'tableActionRecord' => $record,
                                    'record' => $this->record,
                                ]);
                            }),
                        Action::make('refresh')
                            ->label('Refresh')
                            ->icon('heroicon-m-arrow-path')
                            ->color('danger')
                            ->hidden(count($this->serverInfo) > 0)
                            ->action(function () {
                                $this->getDefaultSSH();
                            }),
                    ])
                    ->schema([
                        Infolists\Components\Tabs::make()
                            ->schema([
                                Infolists\Components\Tabs\Tab::make('Info')
                                    ->columns()
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Name Identifier')->copyable()
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('default_cmd')
                                            ->label('Installation Directory')
                                            ->helperText('This command will navigate to the installation directory.')
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->default('not set')
                                            ->copyable()
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500)
                                            ->suffixAction(
                                                Action::make('Run Default Cmd')
                                                    ->icon('heroicon-o-bolt')
                                                    ->color('danger')
                                                    ->label('Execute Installation Directory Command')
                                                    ->requiresConfirmation()
                                                    ->modalHeading('View Files & Folders in the Installation Directory')
                                                    ->modalDescription('Are you ready to proceed with the installation directory command? Please ensure you understand the implications before continuing.')
                                                    ->modalSubmitActionLabel('Yes, Execute')
                                                    ->action(function (): void {
                                                        $this->runInstallationDirectoryCmd();
                                                    })
                                            )
                                            ->hint('Learn more')
                                            ->hintIcon('heroicon-o-information-circle')
                                            ->hintIconTooltip('This command navigates to the installation directory for your restaurant app. You can modify it if needed or keep it for convenience. Click the icon below to view all files and folders on the remote server. We will append \'&& ls -la\' to your command to display the contents of the directory.'),
                                        Infolists\Components\TextEntry::make('host')
                                            ->label('SSH Host')
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->copyable()
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('username')
                                            ->label('SSH Username')
                                            ->copyable()
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('password')
                                            ->label('SSH Password')
                                            ->copyable()
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('port')
                                            ->label('SSH Port')
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->badge()
                                            ->copyable()
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('full_command')
                                            ->color('warning')
                                            ->label('Connection Command')
                                            ->hintIcon('heroicon-o-question-mark-circle')
                                            ->hintIconTooltip('Copy the connection command and paste it into your terminal. After that, copy the password provided above and paste it into the prompt to log in successfully.')
                                            ->default("ssh {$this->defaultSSH?->username}@{$this->defaultSSH?->host} -p {$this->defaultSSH?->port}")
                                            ->icon('heroicon-o-document-duplicate')
                                            ->iconPosition(IconPosition::After)
                                            ->copyable()
                                            ->copyMessage('Copied!')
                                            ->copyMessageDuration(1500)
                                            ->columnSpanFull(),
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Directory Contents')
                                    ->icon('heroicon-o-folder')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('default_cmd')
                                            ->weight(FontWeight::Bold)
                                            ->fontFamily(FontFamily::Mono)
                                            ->label('Directories listed from this command')
                                            ->default("ls -la")
                                            ->icon('heroicon-o-command-line')
                                            ->iconColor('warning')
                                            ->copyable()
                                            ->copyMessage('Copied Command!')
                                            ->copyMessageDuration(1500),
                                        Infolists\Components\TextEntry::make('directories')
                                            ->html()
                                            ->extraAttributes(['class' => 'overflow-x-auto'])
                                            ->prefix(fn() => new HtmlString('<pre>'))
                                            ->suffix(fn() => new HtmlString('</pre><br>'))
                                            ->default(fn() => $this->serverInfo['directories'] ?? 'Not Found'),
                                    ]),
                            ]),
                    ]),

            ]);
    }

    public function serverEnvDetailsInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state($this->serverInfo)
            ->columns(['md' => 12])
            ->schema([
                Infolists\Components\Section::make('Server Environment Details')
                    ->description('Overview of the current software versions running on the server.')
                    ->columns(['sm' => 12])
                    ->collapsible()
                    ->compact()
                    ->columnSpanFull()
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('refresh')
                            ->label('Refresh')
                            ->color('success')
                            ->requiresConfirmation()
                            ->icon('heroicon-o-arrow-path')
                            ->action(function () {
                                $this->getDefaultSSH();
                            }),
                        Infolists\Components\Actions\Action::make('open_website')
                            ->label('Visit Website')
                            ->iconButton()
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url($this->record->domain, true),
                    ])
                    ->footerActionsAlignment(Alignment::End)
                    ->footerActions([
                        Infolists\Components\Actions\Action::make('update')
                            ->label('Update App')
                            ->color('success')
                            ->requiresConfirmation()
                            ->icon('heroicon-o-folder-plus')
                            ->action(function (): void {
                                $this->updateApp();
                            }),
                        Infolists\Components\Actions\Action::make('install')
                            ->requiresConfirmation()
                            ->hidden($this->record?->verified)
                            ->label('Install Restaurant App')
                            ->modalDescription(new HtmlString('Clicking <b>Install</b> will permanently delete all files and folders from the Installation directory and initiate the installation process. Please ensure you have backed up any important data before proceeding.'))
                            ->color('danger')
                            ->icon('heroicon-o-folder-arrow-down')
                            ->action(function (): void {
                                $this->installApp();
                            }),
                    ])
                    ->schema([
                        Infolists\Components\Section::make('Apache')
                            ->description(fn() => $this->serverInfo['apache']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['apache']['icon'])
                            ->iconColor(fn() => $this->serverInfo['apache']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['apache']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('apache.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('apache.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\Section::make('Nginx')
                            ->description(fn() => $this->serverInfo['nginx']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['nginx']['icon'])
                            ->iconColor(fn() => $this->serverInfo['nginx']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['nginx']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('nginx.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('nginx.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\Section::make('Git')
                            ->description(fn() => $this->serverInfo['git']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['git']['icon'])
                            ->iconColor(fn() => $this->serverInfo['git']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['git']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('git.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('git.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\Section::make('MySQL')
                            ->description(fn() => $this->serverInfo['mysql']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['mysql']['icon'])
                            ->iconColor(fn() => $this->serverInfo['mysql']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['mysql']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('mysql.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('mysql.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\Section::make('Composer')
                            ->description(fn() => $this->serverInfo['composer']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['composer']['icon'])
                            ->iconColor(fn() => $this->serverInfo['composer']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['composer']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('composer.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('composer.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\Section::make('PHP')
                            ->description(fn() => $this->serverInfo['php']['description'])
                            ->columns(2)
                            ->icon(fn() => $this->serverInfo['php']['icon'])
                            ->iconColor(fn() => $this->serverInfo['php']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn() => $this->serverInfo['php']['extraAttributes'])
                            ->schema([
                                Infolists\Components\ImageEntry::make('php.logo')
                                    ->square()
                                    ->hiddenLabel()
                                    ->size(60)
                                    ->extraImgAttributes([
                                        'alt' => 'Logo',
                                        'loading' => 'lazy',
                                        'class' => 'rounded-xl',
                                        'style' => 'object-fit:contain;background-color: rgba(1, 1, 1, 0.1);padding: 5px;',
                                    ]),
                                Infolists\Components\TextEntry::make('php.version')
                                    ->label('Version')
                                    ->badge()
                                    ->default('Not Installed'),
                            ]),
                        Infolists\Components\TextEntry::make('modules')
                            ->label('PHP Modules Found')
                            ->default('Not Found')
                            ->columnSpanFull()
                            ->badge(),
                    ]),
            ]);
    }

    public function runManualSSHCommandFormSubmit()
    {
        // headerActions will handle execuation
    }

    private function runSshOnRemoteServer($command)
    {
        $installationDirCommand = $this->getInstallationDirCommand();
        $command = !empty($installationDirCommand) ? "$installationDirCommand && $command" : $command;

        $response = $this->execSimpleSSH($command);

        if ($response->status) {
            $response->icon = 'heroicon-o-command-line';
            $this->sendNotification($response);
        }
        $this->serverInfo['custom_cmd_output'] = $response->plain_body;
    }

    private function installApp()
    {
        $restaurantId = $this->record->id;
        $updateInstallationStatusUrl = route('api.update-installation-status');

        $restaurant = Restaurant::with(['db' => function ($query) {
            $query->whereActive(true)
                ->whereIsValid(true);
        }])->findOrFail($restaurantId);

        // Check if db details not exists then show a notification
        if ($restaurant->db->count() <= 0) {
            $data = new \stdClass;
            $data->title = "Unable to Install Restaurant App";
            $data->body = "No default database details were found for this restaurant. Please set it as the default in the Database Details tab.";
            $data->type = "danger";
            return $this->sendNotification($data);
        }

        // Render the Blade view
        $envContent = view('installation.env', compact('restaurant'))->render();

        // Construct the command to run
        $command = "
            rm -rf ./* .[^.]* && \
            git clone https://github.com/shubhamrajprajapati/restaurant-child.git . && \
            echo '$envContent' > .env && \
            composer install --no-interaction && \
            php artisan key:generate --force >> installation-output.txt 2>&1 && \
            php artisan clear-compiled >> installation-output.txt 2>&1 && \
            npm install >> installation-output.txt 2>&1 && \
            npm run build >> installation-output.txt 2>&1 && \
            php artisan migrate:fresh --seed --force >> installation-output.txt 2>&1 && \
            php artisan storage:link >> installation-output.txt 2>&1 && \
            ls -la >> installation-output.txt 2>&1 && \
            curl -X POST $updateInstallationStatusUrl -d \"id=$restaurantId&status=1\" -H \"Content-Type: application/x-www-form-urlencoded\" >> installation-output.txt 2>&1 || \
            curl -X POST $updateInstallationStatusUrl -d \"id=$restaurantId&status=0\" -H \"Content-Type: application/x-www-form-urlencoded\" >> installation-output.txt 2>&1
        ";

        $response = $this->runInstallationDirectoryCmd(append: $command, return :true);
        $this->serverInfo['directories'] = $response->plain_body;
        $this->serverInfo['custom_cmd_output'] = $response->plain_body;
    }

    private function updateApp()
    {
        $this->runSshOnRemoteServer(
            'git fetch origin main 2>&1 && \
            git rebase origin/main main 2>&1 && \
            composer install --no-dev --optimize-autoloader 2>&1 && \
            php artisan migrate --force 2>&1 && \
            php artisan filament:optimize-clear 2>&1 && \
            npm install 2>&1 && \
            npm run build 2>&1 && \
            php artisan optimize 2>&1 && \
            php artisan filament:optimize 2>&1 && \
            php artisan config:clear 2>&1
        ');
    }

    private function runInstallationDirectoryCmd(?string $command = null, string $append = 'ls -la', bool $showNotification = true, bool $return = false)
    {
        if (!$command) {
            $command = $this->getInstallationDirCommand(); // Perhaps can be empty
        }
        $command = !empty($command) ? "$command && $append" : $append;

        $response = $this->execSimpleSSH($command);

        if ($response->status) {
            $response->icon = 'heroicon-o-command-line';
            if ($showNotification) {
                $this->sendNotification($response);
            }

            if ($return) {
                return $response;
            }
        }

        $this->serverInfo['directories'] = $response->plain_body;
    }
}
