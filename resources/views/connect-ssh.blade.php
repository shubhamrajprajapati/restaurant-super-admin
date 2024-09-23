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
        }
    </style>

    <div class="py-20">
        <h1>Execute SSH Command</h1>
        <input type="text" id="command" placeholder="Enter command" />
        <x-primary-button id="runCommand">Run Command</x-primary-button>
        <pre id="output"></pre>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#runCommand').click(function() {
                const command = $('#command').val();
                $('#output').text(''); // Clear previous output

                $.ajax({
                    url: '/ssh/execute',
                    type: 'POST',
                    data: {
                        command: command,
                        _token: $('meta[name="csrf-token"]').attr('content') // Include CSRF token
                    },
                    xhr: function() {
                        // const xhr = new window.XMLHttpRequest();
                        // // Event listener to handle the response streaming
                        // xhr.onreadystatechange = function() {
                        //     if (xhr.readyState === 3) { // The response is being downloaded
                        //         $('#output').append(xhr.responseText); // Append new output
                        //     }
                        //     if (xhr.readyState === 4) { // The response is being downloaded
                        //         $('#output').append(xhr.responseText); // Append new output
                        //     }
                        //     console.log(xhr);
                        // };
                        // return xhr;

                        const xhr = new window.XMLHttpRequest();
                        // Handle streaming response
                        xhr.onprogress = function() {
                            $('#output').append(xhr.responseText); // Append new output
                            $('#output').scrollTop($('#output')[0]
                            .scrollHeight); // Scroll to bottom
                        };
                        return xhr;
                    },
                    success: function() {
                        // Optionally handle completion
                    },
                    error: function() {
                        $('#output').text('Error running command.');
                    }
                });
            });
        });
    </script>

    @livewire('terminal')
</x-guest-layout>
