<x-filament-panels::page>


    <p>Oops! This resource does not exist. Contact support if this problem persists.</p>
    <x-filament::button tag="a" href="/">Back to
        Dashboard</x-filament::button>
    {{-- {{ $exception->getMessage() }} --}}

    <x-filament::icon alias="panels::topbar.global-search.field" icon="heroicon-m-magnifying-glass" wire:target="search"
        class="h-5 w-5 text-gray-500 dark:text-gray-400" />

    <x-filament::tabs label="Content tabs">
        <x-filament::tabs.item>
            Tab 1
        </x-filament::tabs.item>

        <x-filament::tabs.item>
            Tab 2
        </x-filament::tabs.item>

        <x-filament::tabs.item>
            Tab 3
        </x-filament::tabs.item>
    </x-filament::tabs>

    <x-filament::section>
        <x-slot name="heading">
            User details
        </x-slot>

        {{-- Content --}}
    </x-filament::section>
    <x-filament::loading-indicator class="h-5 w-5" />

    <x-filament::link size="sm">
    New user
</x-filament::link>
 
<x-filament::link size="lg">
    New user
</x-filament::link>
 
<x-filament::link size="xl">
    New user
</x-filament::link>
 
<x-filament::link size="2xl">
    New user
</x-filament::link>

<x-filament::input.wrapper>
    <x-filament::input
        type="text"
        wire:model="name"
    />
</x-filament::input.wrapper>
 
<x-filament::input.wrapper>
    <x-filament::input.select wire:model="status">
        <option value="draft">Draft</option>
        <option value="reviewing">Reviewing</option>
        <option value="published">Published</option>
    </x-filament::input.select>
</x-filament::input.wrapper>

<x-filament::fieldset>
    <x-slot name="label">
        Address
    </x-slot>
    
    {{-- Form fields --}}
</x-filament::fieldset>

<x-filament::breadcrumbs :breadcrumbs="[
    '/' => 'Home',
    '/dashboard' => 'Dashboard',
    '/dashboard/users' => 'Users',
    '/dashboard/users/create' => 'Create User',
]" />

    <div class="p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold mb-4">Server Environment Details</h1>

        @php
            $serverDetails = $this->showServerInfo();
        @endphp

        @if ($serverDetails instanceof stdClass)
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h2 class="text-lg font-medium mb-2">Nginx Version</h2>
                    <p class="text-gray-700">{{ $serverDetails->nginx_version }}</p>
                </div>

                <div class="bg-gray-100 p-4 rounded-lg">
                    <h2 class="text-lg font-medium mb-2">MySQL Version</h2>
                    <p class="text-gray-700">{{ $serverDetails->mysql_version }}</p>
                </div>

                <div class="bg-gray-100 p-4 rounded-lg">
                    <h2 class="text-lg font-medium mb-2">Git Version</h2>
                    <p class="text-gray-700">{{ $serverDetails->git_version }}</p>
                </div>

                <div class="bg-gray-100 p-4 rounded-lg">
                    <h2 class="text-lg font-medium mb-2">PHP Version</h2>
                    <p class="text-gray-700">{{ $serverDetails->php_version }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-medium mb-2">Enabled Modules</h2>
                <ul class="list-disc list-inside bg-gray-100 p-4 rounded-lg max-h-40 overflow-y-auto">
                    @foreach ($serverDetails->modules as $module)
                        <li class="text-gray-700">{{ $module }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>



</x-filament-panels::page>
