<?php

$url = 'https://restaurant-child.jollylifestyle.com/'; // Replace with your URL
$response = file_get_contents($url);

if ($response === false) {
    // Handle error
    echo 'Error fetching the URL.';
} else {
    echo $response; // Output the content
}
