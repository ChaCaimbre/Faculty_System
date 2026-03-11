<?php
header("Content-Type: application/json");
require_once '../config.php';
try {
    $db = getDB();
    $res = $db->query("SELECT (SELECT COUNT(*) FROM schedules) as schedules, (SELECT COUNT(*) FROM faculty) as faculty");
    echo json_encode($res->fetch_assoc());
}
catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
