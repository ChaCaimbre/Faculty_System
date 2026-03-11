<?php
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get all schedules with lab names, faculty names, and subject codes
    $query = "SELECT s.*, r.name as room_name, f.name as faculty_name, f.designated_campus, sub.code as subject_code, sub.name as subject_name 
              FROM schedules s
              JOIN rooms r ON s.room_id = r.id
              JOIN faculty f ON s.faculty_id = f.id
              JOIN subjects sub ON s.subject_id = sub.id
              ORDER BY f.name, s.day, s.start_time ASC";

    $result = $db->query($query);
    $schedules = $result->fetch_all(MYSQLI_ASSOC);

    // Group by teacher
    $grouped = [];
    foreach ($schedules as $s) {
        $teacher = $s['faculty_name'];
        if (!isset($grouped[$teacher])) {
            $grouped[$teacher] = [];
        }
        $grouped[$teacher][] = $s;
    }

    echo json_encode($grouped);
}
else {
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
}
?>
