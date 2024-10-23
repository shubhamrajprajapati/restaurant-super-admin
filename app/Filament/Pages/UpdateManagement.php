<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\HtmlString;

class UpdateManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.pages.update-management';

    protected bool $isUpdateAvailable = false;

    public function mount()
    {
        //
    }

    public function change_log()
    {
        return $this->ansiToHtml(shell_exec('git log --graph'));
    }

    private function checkGitUpdates($showNotification = false)
    {
        // To see if there are new commits on the remote that are not in your local branch:
        // git fetch github && git log main..github/main
        // To check if there are commits in your local branch that are not in the remote: git log github/main..main
        $output = $this->ansiToHtml(shell_exec('git fetch github && git log HEAD..github/main'));
        $this->isUpdateAvailable = empty($output) ? false : true;

        if ($showNotification) {
            Notification::make('check_for_updates_notification')
                ->title(fn() => $this->isUpdateAvailable ? 'Update Available!' : 'No Updates Available')
                ->body(fn() => $this->isUpdateAvailable ? 'A new update is ready to install. Click the button below to install the latest features and improvements.' : 'Your application is up to date! You are currently using the latest version with all features and improvements.')
                ->color(fn() => $this->isUpdateAvailable ? 'success' : 'info')
                ->icon(fn() => $this->isUpdateAvailable ? 'herocion-o-check-circle' : 'heroicon-o-information-circle')
                ->send();
        }

        return $output;
    }

    private function getFormattedTime(): string
    {
        return Carbon::now()->format('h:i A T');
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

    private function installAndUpdateNow()
    {
        $commandToUpdate = app()->environment('local') ? null : '&& git rebase github/main main';
        $output = $this->ansiToHtml(shell_exec("git fetch github $commandToUpdate"));
        $this->checkGitUpdates();

        Notification::make('install_and_update_now_notification')
            ->title('Update Installed Successfully!')
            ->body(join(["The latest update has been installed. Thank you for keeping your application up to date! You can now enjoy the new features and improvements.", empty($output) ? null : "<br><br><div class='overflow-x-auto'><pre>{$output}</pre><br><div>"]))
            ->success()
            ->send();
    }

    public function checkForUpdatesInfolist(Infolist $infolist)
    {
        return $infolist
            ->state([
                'updates' => $this->checkGitUpdates(),
                'heading' => fn() => $this->isUpdateAvailable ? 'Updates available' : 'You\'re up to date',
                'deployment_history' => 'Deployment History: Recent Changes',
            ])
            ->schema([
                Infolists\Components\Section::make()
                    ->heading('Website Updates')
                    ->compact()
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('Optimize')
                            ->label('Optimize Now')
                            ->icon('heroicon-o-bolt')
                            ->size(ActionSize::ExtraSmall)
                            ->color('success')
                            ->action(function () {
                                $notification = new \stdClass();
                                try {
                                    $applicationOptimize = shell_exec('cd .. && php artisan optimize');
                                    $filamentOptimize = shell_exec('cd .. && php artisan filament:optimize');

                                    $notification->title = "Optimization Completed Successfully!";
                                    $notification->body = "<div class='overflow-x-auto'><pre>{$applicationOptimize} {$filamentOptimize}</pre><br></div>";
                                    $notification->type = 'success';
                                } catch (\Exception $e) {
                                    $notification->title = "Some error occurred!";
                                    $notification->body = "<div class='overflow-x-auto'><pre>{$e->getMessage()}</pre><br></div>";
                                    $notification->type = 'danger';
                                    $this->sendNotification($notification);
                                }

                                return $this->sendNotification($notification);
                            }),
                        Infolists\Components\Actions\Action::make('Clear_Optimize')
                            ->label('Clear Optimization')
                            ->icon('heroicon-o-bolt-slash')
                            ->size(ActionSize::ExtraSmall)
                            ->color('danger')
                            ->action(function () {
                                $applicationOptimize = shell_exec('cd .. && php artisan optimize:clear');
                                $filamentOptimize = shell_exec('cd .. && php artisan filament:optimize-clear');

                                $notification = new \stdClass();
                                $notification->title = "Optimization Cleared Successfully!";
                                $notification->body = "<div class='overflow-x-auto'><pre>{$applicationOptimize} {$filamentOptimize}</pre><br></div>";
                                $notification->type = 'success';

                                return $this->sendNotification($notification);
                            }),
                    ])
                    ->collapsible()
                    ->schema([
                        Infolists\Components\TextEntry::make('heading')
                            ->icon(fn() => $this->isUpdateAvailable ? 'heroicon-o-arrow-path' : 'heroicon-s-check-circle')
                            ->color(fn() => $this->isUpdateAvailable ? 'info' : 'success')
                            ->iconColor(fn() => $this->isUpdateAvailable ? 'info' : 'success')
                            ->hiddenLabel()
                            ->helperText(fn() => 'Last checked: Today, ' . $this->getFormattedTime())
                            ->size(TextEntrySize::Large)
                            ->suffixActions([
                                Infolists\Components\Actions\Action::make('install_update')
                                    ->visible($this->isUpdateAvailable)
                                    ->label('Install Update')
                                    ->color('success')
                                    ->icon('heroicon-o-folder-arrow-down')
                                    ->button()
                                    ->requiresConfirmation()
                                    ->modalHeading('Ready to Install Update')
                                    ->modalDescription('You are about to install the latest update. This will include new features, improvements, and bug fixes. Please ensure that you save your work before proceeding.')
                                    ->modalSubmitActionLabel('Install Now')
                                    ->action(function () {
                                        $this->installAndUpdateNow();
                                    }),
                                Infolists\Components\Actions\Action::make('check_for_update')
                                    ->hidden($this->isUpdateAvailable)
                                    ->label('Check for updates')
                                    ->icon('heroicon-o-arrow-path')
                                    ->button()
                                    ->action(function (): void {
                                        $this->checkGitUpdates(true);
                                    }),
                            ]
                            ),
                        Infolists\Components\TextEntry::make('updates')
                            ->visible($this->isUpdateAvailable)
                            ->html()
                            ->extraAttributes(['class' => 'overflow-x-auto'])
                            ->prefix(fn() => new HtmlString('<pre>'))
                            ->suffix(fn() => new HtmlString('</pre><br>')),
                    ]),
                Infolists\Components\TextEntry::make('deployment_history')
                    ->hiddenLabel()
                    ->size(TextEntrySize::Large)
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->helperText('This section summarizes the recent updates made to the application. Each entry includes the commit hash, author, date, and a brief description of the changes implemented.'),
            ]);
    }

    protected function ansiToHtml($text)
    {
        if ($text) {
            // Escape HTML special characters
            $text = htmlspecialchars($text);

            // Define ANSI color codes and styles
            $ansiColors = [
                '/\e\[30m/' => '<span style="color: black;">',
                '/\e\[31m/' => '<span style="color: red;">',
                '/\e\[32m/' => '<span style="color: green;">',
                '/\e\[33m/' => '<span style="color: yellow;">',
                '/\e\[34m/' => '<span style="color: blue;">',
                '/\e\[35m/' => '<span style="color: magenta;">',
                '/\e\[36m/' => '<span style="color: cyan;">',
                '/\e\[37m/' => '<span style="color: white;">',
                '/\e\[90m/' => '<span style="color: gray;">', // Bright black (gray)
                '/\e\[91m/' => '<span style="color: lightcoral;">', // Bright red
                '/\e\[92m/' => '<span style="color: lightgreen;">', // Bright green
                '/\e\[93m/' => '<span style="color: lightyellow;">', // Bright yellow
                '/\e\[94m/' => '<span style="color: lightblue;">', // Bright blue
                '/\e\[95m/' => '<span style="color: pink;">', // Bright magenta
                '/\e\[96m/' => '<span style="color: lightcyan;">', // Bright cyan
                '/\e\[97m/' => '<span style="color: lightgray;">', // Bright white
                '/\e\[0m/' => '</span>', // Reset
                '/\e\[1m/' => '<strong>', // Bold
                '/\e\[22m/' => '</strong>', // End bold
                '/\e\[4m/' => '<u>', // Underline
                '/\e\[24m/' => '</u>', // End underline
            ];

            // Replace ANSI codes with HTML spans
            foreach ($ansiColors as $pattern => $replacement) {
                $text = preg_replace($pattern, $replacement, $text);
            }

            // Handle special characters for graph
            $text = str_replace(['*', '|', '\\'], ['&#9733;', '&#124;', '&#92;'], $text); // Replace graph symbols
        }

        return $text;
    }
}
