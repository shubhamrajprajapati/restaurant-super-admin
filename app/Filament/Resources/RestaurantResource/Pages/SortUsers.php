<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Models\RestaurantSSHDetails;
use App\Services\SSHService;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class SortUsers extends Page implements HasForms
{
    use InteractsWithRecord;

    protected static string $resource = RestaurantResource::class;

    protected static string $view = 'filament.resources.restaurant-resource.pages.sort-users';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $title = 'Check Restaurant Server Checkup';

    protected static ?string $navigationLabel = 'Server Checkup';

    protected function connectSSH($ssh)
    {
        try {
            return new SSHService(
                $ssh->host,
                $ssh->port,
                $ssh->username,
                $ssh->password
            );
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        } finally {
            Notification::make('sdds')
                ->success()
                ->title('SSH Connected Successfully.')
                ->send();
        }
    }

    public function showServerInfo()
    {
        if ($this->record?->ssh instanceof RestaurantSSHDetails) {
            // dd($this->record->ssh->attributesToArray());
            $sshConnected = $this->connectSSH($this->record->ssh);

            if (! $sshConnected->isConnected()) {
                return 'Error: SSH connection not established.';
            }

            try {
                $command = file_get_contents(base_path('installation/system-check-inline.sh'));

                return json_decode($sshConnected->executeSimpleCommand($command));
            } catch (\Exception $e) {
                return 'Error: '.$e->getMessage();
            }
        } else {
            return 'Not SSH Instance Found';
        }
    }
}
