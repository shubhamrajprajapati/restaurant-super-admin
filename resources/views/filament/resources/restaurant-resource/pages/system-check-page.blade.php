<x-filament-panels::page>
    @if ($this->defaultSSH->id)
        {{ $this->defaultSSHInfolist }}
        {{ $this->serverEnvDetailsInfolist }}
        <x-filament-panels::form wire:submit="runManualSSHCommandFormSubmit">
            {{ $this->form }}
        </x-filament-panels::form>
    @else
        {{ $this->table }}
    @endif

    <x-filament-actions::modals />

    <div x-data="{ data: '' }" x-on:post-created.window="console.log('listening...')"></div>
    <div x-data="{ data: '' }" x-on:post-createdd.window="console.log($event.detail[0].title)"></div>

    <div>
        <h1>Your Livewire Component</h1>
        <button wire:click="startDispatching">Start Dispatching</button>
    </div>

</x-filament-panels::page>
