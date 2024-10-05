<?php

namespace App\Services;

use App\Models\RestaurantSSHDetails;
use Filament\Notifications\Notification;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SSH2;

class SSHService
{
    public $ssh;

    public function __construct(?RestaurantSSHDetails $ssh)
    {
        if ($ssh instanceof RestaurantSSHDetails) {
            $this->connectManually($ssh->host, $ssh->port, $ssh->username, $ssh->password);
        }
    }

    public function connectManually($ssh_server, $ssh_port, $ssh_username, $ssh_password)
    {
        try {
            $this->ssh = new SSH2($ssh_server, $ssh_port);
            return $this->ssh->login($ssh_username, $ssh_password);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function executeSimpleCommand($command, $realtime = false)
    {
        if (!$realtime) {
            return $this->ssh->exec($command);
        } else {
            return $this->ssh->exec($command, fn ($output) => $output);
        }
    }

    public function executeIntractiveCommand($command)
    {
        $this->ssh->enablePTY();

        $this->ssh->write($command . "\n", SSH2::CHANNEL_SHELL);

        $output = $this->ssh->read('/username@username:~\$/', SSH2::READ_REGEX, SSH2::CHANNEL_SHELL);

        // Use ANSI class to handle ANSI codes and get formatted output
        $ansi = new ANSI;
        $ansi->appendString($output);

        return $ansi->getScreen(); // Outputs formatted HTML
    }

    public function isConnected()
    {
        return $this->ssh->isConnected();
    }

    public function disconnect()
    {
        if ($this->ssh) {
            $this->ssh->disconnect();
        }
    }
}
