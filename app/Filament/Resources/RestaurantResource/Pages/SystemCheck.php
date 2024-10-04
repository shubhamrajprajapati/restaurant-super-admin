<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Models\RestaurantSSHDetails;
use App\Services\SSHService;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $listeners = ['sshUpdated' => 'getDefaultSSH'];

    public function mount(int|string $record)
    {
        $this->record = $this->resolveRecord($record);
        $this->sshCount = $this->record?->ssh?->count();
        $this->getDefaultSSH();
    }

    private function connectSSH()
    {
        return new SSHService($this->defaultSSH);
    }

    private function execSimpleSSH($command)
    {
        try {
            $sshConnected = $this->connectSSH();
            if (!$sshConnected->isConnected()) {
                return 'Error: SSH connection not established.';
            }
            return $sshConnected->executeSimpleCommand($command);
        } catch (\Exception $e) {
            return 'Error: SSH execuation failed.' . $e->getMessage();
        }
    }

    public function getDefaultSSH()
    {
        $this->defaultSSH = $this->record->ssh()
            ->whereActive(true)
            ->whereIsValid(true)
            ->first() ?? new RestaurantSSHDetails;

        if ($this->defaultSSH?->id) {
            $command = file_get_contents(base_path('installation/system-check-inline.sh'));
            $output = $this->execSimpleSSH($command);
            $this->serverInfo = json_decode($output, true);
            $this->arrangeDefaultSSH();
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
        $this->serverInfo[$key]['logo'] = asset("assets/images/server-logos/$key.webp");
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
                    ->statePath('data')
                    ->heading('Run commands')
                    ->description('Now, you can run commands directly on this remote server.')
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
                            ->action(function () {
                                $data = (object) $this->form->getState()['data'];
                                $output = $this->execSimpleSSH($data->command);
                                Notification::make()
                                    ->title("Command Executed Successfully: '$data->command'")
                                    ->body($output)
                                    ->success()
                                    ->icon('heroicon-o-command-line')
                                    ->send();
                            })

                    ])
                    ->schema([
                        Forms\Components\TextInput::make('command')
                            ->hiddenLabel()
                            ->placeholder('Enter command like- ls, cd www, etc'),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        $resSSH = new ManageRestaurantSSH;

        return $resSSH->table($table)
            ->relationship(fn (): HasMany => $this->record->ssh())
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
            ->emptyStateHeading(fn (Table $table) => $table->getHeading())->emptyStateDescription(fn (Table $table) => $table->getDescription())
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
                    ->columns()
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->headerActions([
                        Action::make('edit')
                            ->label('Change Default SSH')
                            ->icon('heroicon-m-pencil-square')
                            ->url(function (RestaurantSSHDetails $record) {
                                return ManageRestaurantSSH::getUrl(parameters: [
                                    'tableAction' => EditAction::getDefaultName(),
                                    'tableActionRecord' => $record,
                                    'record' => $this->record,
                                ]);
                            }),
                    ])
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name Identifier')->copyable()
                            ->icon('heroicon-o-document-duplicate')
                            ->iconPosition(IconPosition::After)
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),
                        Infolists\Components\TextEntry::make('default_cmd')
                            ->label('Default Command')
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
                                    ->label('Run Default Command')
                                    ->requiresConfirmation()
                                    ->modalHeading('Execute Default Command')
                                    ->modalDescription('Are you ready to proceed with the default command? Ensure that you understand the implications before continuing.')
                                    ->modalSubmitActionLabel('Yes, excute')
                                    ->action(function (Action $action) {
                                        $command = $action->getRecord()->default_cmd;
                                        $cmdOutput = $this->execSimpleSSH($command);
                                        Notification::make()
                                            ->title("Command Executed Successfully: '$command'")
                                            ->success()
                                            ->icon('heroicon-o-command-line')
                                            ->body($cmdOutput)
                                            ->send();
                                    })
                            )
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip('Click on the below icon to run default command on this remote server.'),
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
                    ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
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
                        Infolists\Components\Actions\Action::make('install')
                            ->label('Install Restaurant App')
                            ->requiresConfirmation()
                            ->icon('heroicon-o-folder-arrow-down')
                            ->action(function () {
                                return true;
                            }),
                    ])
                    ->schema([
                        Infolists\Components\Section::make('Apache')
                            ->description(fn () => $this->serverInfo['apache']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['apache']['icon'])
                            ->iconColor(fn () => $this->serverInfo['apache']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['apache']['extraAttributes'])
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
                            ->description(fn () => $this->serverInfo['nginx']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['nginx']['icon'])
                            ->iconColor(fn () => $this->serverInfo['nginx']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['nginx']['extraAttributes'])
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
                            ->description(fn () => $this->serverInfo['git']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['git']['icon'])
                            ->iconColor(fn () => $this->serverInfo['git']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['git']['extraAttributes'])
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
                            ->description(fn () => $this->serverInfo['mysql']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['mysql']['icon'])
                            ->iconColor(fn () => $this->serverInfo['mysql']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['mysql']['extraAttributes'])
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
                            ->description(fn () => $this->serverInfo['composer']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['composer']['icon'])
                            ->iconColor(fn () => $this->serverInfo['composer']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['composer']['extraAttributes'])
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
                            ->description(fn () => $this->serverInfo['php']['description'])
                            ->columns(2)
                            ->icon(fn () => $this->serverInfo['php']['icon'])
                            ->iconColor(fn () => $this->serverInfo['php']['color'])
                            ->columnSpan(['sm' => 6, 'xl' => 4])
                            ->collapsible()
                            ->compact()
                            ->extraAttributes(fn () => $this->serverInfo['php']['extraAttributes'])
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
                            ->columnSpanFull()
                            ->badge(),
                    ]),
            ]);
    }

    public function runManualSSHCommandFormSubmit()
    {
        // headerActions will handle execuation
    }
}
