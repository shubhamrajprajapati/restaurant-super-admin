<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UpdateManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.pages.update-management';

    protected $data = [];

    public function mount()
    {
        $this->data = [
            'change_log' => $this->ansiToHtml(shell_exec('git log --graph')),
        ];
    }

    protected function ansiToHtml($text)
    {
        // Escape HTML special characters
        $text = htmlspecialchars($text);

        // Define ANSI color codes and styles
        $ansiColors = [
            '/\e\[30m/' => '<span style="color: black;">',
            '/\e\[31m/' => '<span style="color: red;">',
            '/\e\[32m/' => '<span style="color: green;">',
            '/\e\[33m/' => '<span style="color: yellow;">',
            '/\e\[34m/' => '<span style="color: blue;">',
            '/\e\[35m/' => '<span style="color: magenta;">',
            '/\e\[36m/' => '<span style="color: cyan;">',
            '/\e\[37m/' => '<span style="color: white;">',
            '/\e\[90m/' => '<span style="color: gray;">', // Bright black (gray)
            '/\e\[91m/' => '<span style="color: lightcoral;">', // Bright red
            '/\e\[92m/' => '<span style="color: lightgreen;">', // Bright green
            '/\e\[93m/' => '<span style="color: lightyellow;">', // Bright yellow
            '/\e\[94m/' => '<span style="color: lightblue;">', // Bright blue
            '/\e\[95m/' => '<span style="color: pink;">', // Bright magenta
            '/\e\[96m/' => '<span style="color: lightcyan;">', // Bright cyan
            '/\e\[97m/' => '<span style="color: lightgray;">', // Bright white
            '/\e\[0m/' => '</span>', // Reset
            '/\e\[1m/' => '<strong>', // Bold
            '/\e\[22m/' => '</strong>', // End bold
            '/\e\[4m/' => '<u>', // Underline
            '/\e\[24m/' => '</u>', // End underline
        ];

        // Replace ANSI codes with HTML spans
        foreach ($ansiColors as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        // Handle special characters for graph
        $text = str_replace(['*', '|', '\\'], ['&#9733;', '&#124;', '&#92;'], $text); // Replace graph symbols

        return $text;
    }
}
