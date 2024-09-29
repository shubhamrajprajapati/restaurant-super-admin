<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use phpseclib3\File\ANSI;

class SSHService
{
    public $ssh;

    public function __construct($ssh_server, $ssh_port, $ssh_username, $ssh_password)
    {
        $this->connect($ssh_server, $ssh_port, $ssh_username, $ssh_password);
    }

    public function connect($ssh_server, $ssh_port, $ssh_username, $ssh_password)
    {
        $this->ssh = new SSH2($ssh_server, $ssh_port);

        if (!$this->ssh->login($ssh_username, $ssh_password)) {
            throw new \Exception('SSH authentication failed!');
        }
    }

    public function executeSimpleCommand($command)
    {
        $batchContent = file_get_contents(base_path('installation/system-check-inline.sh'));
        // return $batchContent;
        return dd(json_decode($this->ssh->exec($batchContent)));
    }

    public function executeIntractiveCommand($command)
    {
        $this->ssh->enablePTY(); 

        $this->ssh->write($command . "\n", SSH2::CHANNEL_SHELL);

        $output = $this->ssh->read('/username@username:~\$/', SSH2::READ_REGEX, SSH2::CHANNEL_SHELL);

        // Use ANSI class to handle ANSI codes and get formatted output
        $ansi = new ANSI();
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
