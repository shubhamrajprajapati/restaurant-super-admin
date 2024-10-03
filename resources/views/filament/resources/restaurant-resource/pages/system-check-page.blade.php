<x-filament-panels::page>
    @if ($this->defaultSSH->id)
        {{ $this->defaultSSHInfolist }}
    @else
        {{ $this->table }}
    @endif
    {{ $this->infolist }}

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>

</x-filament-panels::page>
