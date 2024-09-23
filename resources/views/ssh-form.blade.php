<x-guest-layout>
    <div class="container">
        <h1 class="text-lg text-center">SSH Check</h1>

        <form action="{{ route('check.ssh') }}" method="POST" class="space-x-3">
            @csrf

            <div class="mb-3">
                <x-input-label for="ssh_server">SSH Server</x-input-label>
                <x-text-input type="text" class="w-full" id="ssh_server" name="ssh_server" required />
            </div>

            <div class="mb-3">
                <x-input-label for="ssh_port" class="form-label">SSH Port</x-input-label>
                <x-text-input type="number" class="w-full" id="ssh_port" name="ssh_port" required />
            </div>

            <div class="mb-3">
                <x-input-label for="ssh_username" class="form-label">SSH Username</x-input-label>
                <x-text-input type="text" class="w-full" id="ssh_username" name="ssh_username" required />
            </div>

            <div class="mb-3">
                <x-input-label for="ssh_password" class="form-label">SSH Password</x-input-label>
                <x-text-input type="password" class="w-full" id="ssh_password" name="ssh_password" required />
            </div>

            <x-primary-button>Check SSH</x-primary-button>
        </form>

        @if (session('result'))
            <div class="mt-4">
                <h3>Result:</h3>
                <pre>{{ session('result') }}</pre>
            </div>
        @endif
    </div>
</x-guest-layout>
