<?php
// Get the raw POST data
$json = file_get_contents('php://input');

// Decode the JSON data
$data = json_decode($json, true);

// Write the data to a file
if (file_put_contents('system_info.json', json_encode($data, JSON_PRETTY_PRINT))) {
    echo 'Data received and saved';
} else {
    http_response_code(500);
    echo 'Failed to save data';
}
?>
