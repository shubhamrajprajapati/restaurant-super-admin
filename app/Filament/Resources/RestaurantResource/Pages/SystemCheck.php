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

    public function getDefaultSSH()
    {
        $this->defaultSSH = $this->record->ssh()
            ->whereActive(true)
            ->whereIsValid(true)
            ->first() ?? new RestaurantSSHDetails;

        if ($this->defaultSSH?->id) {
            // Notification::make()->success()->title('SSH Can be loaded now.
            // ')->send();

            try {
                $sshConnected = new SSHService($this->defaultSSH);

                if (!$sshConnected->isConnected()) {
                    return 'Error: SSH connection not established.';
                }

                $command = file_get_contents(base_path('installation/system-check-inline.sh'));

                $this->serverInfo = json_decode($sshConnected->executeSimpleCommand($command), true);
                $this->arrangeDefaultSSH();
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
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
        return $form->schema([
            // Forms\Components\TextInput::make('name'),
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
                            ->copyMessageDuration(1500),
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
                            ->icon('heroicon-o-folder-arrow-down'),
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
}
