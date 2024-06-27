<?php
// Get the raw POST data
$json = file_get_contents('php://input');

// Decode the JSON data
$data = json_decode($json, true);

// Check if data is valid
if (!$data || !is_array($data)) {
    http_response_code(400);
    echo 'Invalid JSON data';
    exit;
}

// Ensure systems directory exists
$systemsDir = 'systems/';
if (!file_exists($systemsDir)) {
    mkdir($systemsDir, 0777, true); // Create the directory if it doesn't exist
}

// Loop through each record and write to separate JSON files
foreach ($data as $key => $record) {
    // Create a filename based on the key
    $filename = $systemsDir . $key . '.json';

    // Write the record to the file
    if (file_put_contents($filename, json_encode($record, JSON_PRETTY_PRINT))) {
        echo "Data for key '$key' saved to $filename <br>";
    } else {
        echo "Failed to save data for key '$key' <br>";
    }
}
?>
