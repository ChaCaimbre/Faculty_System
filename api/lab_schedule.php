<?php
header("Content-Type: application/json");
session_start();
require_once '../config.php';
require_once 'auth_helper.php';

$db  = getDB();
$uid = requireUserId();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 1;
    $query = "SELECT s.*, r.name as room_name, f.name as faculty_name, f.designated_campus,
                     sub.code as subject_code, sub.name as subject_name 
              FROM schedules s
              JOIN rooms r    ON s.room_id    = r.id
              JOIN faculty f  ON s.faculty_id = f.id
              JOIN subjects sub ON s.subject_id = sub.id
              WHERE s.user_id = $uid AND s.term_id = $term_id
              ORDER BY FIELD(s.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), s.start_time ASC";

    $result = $db->query($query);
    $schedules = $result->fetch_all(MYSQLI_ASSOC);

    $grouped = [];
    foreach ($schedules as $s) {
        $lab = $s['room_name'];
        if (!isset($grouped[$lab])) $grouped[$lab] = [];
        $grouped[$lab][] = $s;
    }
    echo json_encode($grouped);
} else {
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
}
?>
