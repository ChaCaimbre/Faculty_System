<?php
/**
 * Cleanup helper:
 * - Removes temporary room entries we accidentally created (AI21, AI25, AI32, AI35, AI36, MT12)
 * - Deletes any schedules that use those rooms.
 *
 * Run once via:
 *   c:\xampp\php\php.exe cleanup_temp_rooms.php
 * or in browser:
 *   http://localhost/Faculty_System/cleanup_temp_rooms.php
 */

require_once 'config.php';

$db = getDB();

$roomNames = ['AI21', 'AI25', 'AI32', 'AI35', 'AI36', 'MT12'];

$deletedSchedules = 0;
$deletedRooms = 0;

foreach ($roomNames as $name) {
    $safe = $db->real_escape_string($name);
    $res = $db->query("SELECT id FROM rooms WHERE name = '$safe'");
    if (!$res || $res->num_rows === 0) {
        continue;
    }

    while ($row = $res->fetch_assoc()) {
        $rid = (int) $row['id'];
        $db->query("DELETE FROM schedules WHERE room_id = $rid");
        $deletedSchedules += $db->affected_rows;

        $db->query("DELETE FROM rooms WHERE id = $rid");
        if ($db->affected_rows > 0) {
            $deletedRooms++;
        }
    }
}

$result = [
    'deleted_rooms' => $deletedRooms,
    'deleted_schedules' => $deletedSchedules,
];

if (php_sapi_name() === 'cli') {
    echo "Deleted rooms: {$result['deleted_rooms']}\n";
    echo "Deleted schedules: {$result['deleted_schedules']}\n";
} else {
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}

