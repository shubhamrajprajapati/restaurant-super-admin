<x-filament-panels::page>
    @if ($this->defaultSSH->id)
        {{ $this->defaultSSHInfolist }}
        @if (count($this->serverInfo))
            {{ $this->serverEnvDetailsInfolist }}
            <x-filament-panels::form wire:submit="runManualSSHCommandFormSubmit">
                {{ $this->form }}
            </x-filament-panels::form>
        @endif
    @else
        {{ $this->table }}
    @endif

    <x-filament-actions::modals />

</x-filament-panels::page>
