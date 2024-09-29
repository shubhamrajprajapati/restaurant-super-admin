<x-guest-layout>
    <style>
        .terminal-container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .ssh-form,
        .command-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .command-output {
            background-color: #2e2e2e;
            color: white;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            max-width:700px;
        }
    </style>
    
    @livewire('terminal')
</x-guest-layout>
