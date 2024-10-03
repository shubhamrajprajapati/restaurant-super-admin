<?php

namespace App\Http\Controllers;

use App\Services\SSHService;
use Illuminate\Http\Request;

class SSHController extends Controller
{
    /**
     * Show the SSH form.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('ssh-form');
    }

    /**
     * Check SSH connectivity and execute a command.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Handle the SSH form submission.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkSSH(Request $request)
    {
        $validated = $request->validate([
            'ssh_server' => 'required|string',
            'ssh_port' => 'required|integer',
            'ssh_username' => 'required|string',
            'ssh_password' => 'required|string',
        ]);

        try {
            $sshService = new SSHService(
                $validated['ssh_server'],
                $validated['ssh_port'],
                $validated['ssh_username'],
                $validated['ssh_password']
            );

            $output = $sshService->executeSimpleCommand('cd htdocs && ls -a');

            return back()->with('result', $output);
        } catch (\Exception $e) {
            return back()->with('result', 'Error: '.$e->getMessage());
        }
    }

    public function executeSimpleCommand(Request $request)
    {
        $command = $request->input('command');
        $sshService = new SSHService(
            'restaurant-child.jollylifestyle.com',
            '22',
            'jollylifestyle-restaurant-child',
            'Shubham@123',
        );

        if (ob_get_level()) {
            ob_end_flush(); // End any existing output buffering
        }

        // Stream output
        ob_start(); // Start output buffering

        $sshService->ssh->exec($command, function ($str) {
            echo $str;
            flush(); // Flush the output buffer
            ob_flush(); // Ensure output is sent immediately
        });

        ob_end_flush(); // End output buffering
        $sshService->ssh->disconnect();
    }
}
