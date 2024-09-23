<div class="terminal-container">
    <x-danger-button wire:click="showSessionCMD">Show Session Command</x-danger-button>
    @if ($loading)
        <div class="loading">
            <div class="spinner-border" role="status"></div>
            <span>Connecting...</span>
        </div>
    @else
        @if (!$connected)
            <form wire:submit.prevent="connectSSH" class="ssh-form">
                <x-text-input type="text" wire:model="ssh_server" placeholder="SSH Server" required/>
                <x-text-input type="number" wire:model="ssh_port" placeholder="SSH Port" required/>
                <x-text-input type="text" wire:model="ssh_username" placeholder="Username" required/>
                <x-text-input type="password" wire:model="ssh_password" placeholder="Password" required/>
                <x-secondary-button type="submit">Connect</x-secondary-button>
            </form>
        @else
            <div class="command-output">
               {!! $output !!}
            </div>
            <form wire:submit.prevent="executeCommand" class="command-form">
                <x-text-input type="text" wire:model="command" placeholder="Enter command..." required/>
                <x-secondary-button type="submit">Execute</x-secondary-button>
            </form>
        @endif
    @endif
</div>