<x-filament-panels::page>
    @if ($this->defaultSSH->id)
        {{ $this->defaultSSHInfolist }}
        {{ $this->infolist }}
        <x-filament-panels::form wire:submit="runManualSSHCommandFormSubmit">
            {{ $this->form }}
        </x-filament-panels::form>
    @else
        {{ $this->table }}
    @endif

    <x-filament-actions::modals />

</x-filament-panels::page>
