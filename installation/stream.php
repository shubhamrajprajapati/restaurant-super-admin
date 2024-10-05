<?php

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD");

header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


ob_end_flush();

while (true) {
    echo "event: message\n";
    echo "data: sdsdhdhsgsf\n";

    if (connection_aborted()) {
        break;
    }

    sleep(1);
}

echo "event: stop\n";
echo "data: stopped\n\n";

exit;

// Realtime testing output feature - can be removed
function runCommand($command)
{
    // Establish SSH connection
    $ssh = new SSH2("restaurant-child.jollylifestyle.com", '22');
    if (!$ssh->login('jollylifestyle-restaurant-child', 'Shubham@123')) {
        return "Falied";
    }

    // while command to run 5 times
    for ($i = 0; $i < 5; $i++) {
        $this->dispatch('post-created');
        usleep(3000000); // 5 seconds in microseconds
    }

    // Execute the command with a callback for real-time output
    // $ssh->exec($command, function ($output) {
    //     $this->dispatch('post-created');
    //     @flush(); // Flush the output buffer
    //     @ob_flush(); // Flush PHP's output buffer
    //     // Simulating a long-running process, sleep for 5 seconds
    //     usleep(1000000); // 5 seconds in microseconds
    //     Notification::make()->title($output)->send();
    // });
}

function startDispatching()
{
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"],  // stderr
    ];

    $process = proc_open('ping google.com', $descriptorspec, $pipes);

    if (is_resource($process)) {
        while ($line = fgets($pipes[1])) {
            $this->dispatch('post-createdd', ['title' => $line]);
            ob_flush();
            flush();
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $return_value = proc_close($process);
        $this->dispatch('post-createdd', ['title' => $return_value]);
    }
}
