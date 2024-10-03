<x-filament-panels::page>
    @if ($this->defaultSSH->id)
        {{ $this->defaultSSHInfolist }}
        {{ $this->infolist }}
    @else
        {{ $this->table }}
    @endif

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>

</x-filament-panels::page>
