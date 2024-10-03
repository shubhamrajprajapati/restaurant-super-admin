<?php

namespace App\Livewire;

use App\Services\SSHService;
use Livewire\Component;

class Terminal extends Component
{
    public $ssh_server = 'restaurant-child.jollylifestyle.com';

    public $ssh_port = 22;

    public $ssh_username = 'jollylifestyle-restaurant-child';

    public $ssh_password = 'Shubham@123';

    public $command;

    public $output = '';

    public $loading = false;

    public $connected = false;

    protected $sshService;

    public function mount()
    {
        $this->connectSSH();
    }

    public function connectSSH()
    {
        $this->loading = true;

        try {
            $sftp = new SSHService(
                $this->ssh_server,
                $this->ssh_port,
                $this->ssh_username,
                $this->ssh_password
            );

            $this->sshService = $sftp;

            $this->connected = true;
            $this->output = 'Connected successfully.';
        } catch (\Exception $e) {
            $this->output = 'Error: '.$e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function executeCommand()
    {
        $this->loading = true;
        $this->connectSSH();

        if (! $this->connected || ! $this->sshService->isConnected()) {
            $this->output = 'Error: SSH connection not established.';
            $this->loading = false;

            return;
        }

        try {
            $this->output = $this->sshService->executeSimpleCommand($this->command);
        } catch (\Exception $e) {
            $this->output = 'Error: '.$e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.terminal');
    }
}
