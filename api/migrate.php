<?php
require_once '../config.php';
$db = getDB();

$res = $db->query("SHOW COLUMNS FROM faculty LIKE 'employment_status'");
if ($res->num_rows === 0) {
    if ($db->query("ALTER TABLE faculty ADD COLUMN employment_status VARCHAR(50) DEFAULT 'Full-time'")) {
        echo json_encode(["success" => true, "message" => "Column added"]);
    }
    else {
        echo json_encode(["success" => false, "error" => $db->error]);
    }
}
else {
    echo json_encode(["success" => true, "message" => "Column already exists"]);
}
?>
