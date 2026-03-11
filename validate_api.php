<?php
$json = file_get_contents('http://localhost/api/lab_schedule.php');
$data = json_decode($json, true);
if ($data === null) {
    echo "Invalid JSON: " . json_last_error_msg() . "\n";
    echo "Output: " . substr($json, 0, 100) . "...\n";
} else {
    echo "Valid JSON. Labs: " . count($data) . "\n";
    foreach ($data as $lab => $scheds) {
        echo "- $lab: " . count($scheds) . " schedules\n";
    }
}
?>
