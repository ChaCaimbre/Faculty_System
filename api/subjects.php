<?php
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $result = $db->query("SELECT * FROM subjects ORDER BY code ASC");
        $subjects = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($subjects);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $code = $db->real_escape_string($data['code']);
        $name = $db->real_escape_string($data['name']);
        $units = intval($data['units']);
        $room_id = isset($data['room_id']) && $data['room_id'] !== '' ? intval($data['room_id']) : null;

        $db->begin_transaction();
        try {
            $query = "INSERT INTO subjects (code, name, units) VALUES ('$code', '$name', $units)";
            $db->query($query);
            $subject_id = $db->insert_id;

            if ($room_id) {
                $db->query("INSERT INTO schedules (subject_id, room_id) VALUES ($subject_id, $room_id)");
            }
            $db->commit();
            echo json_encode(["success" => true, "id" => $subject_id]);
        }
        catch (Exception $e) {
            $db->rollback();
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents("php://input"), true);
        $code = $db->real_escape_string($data['code']);
        $name = $db->real_escape_string($data['name']);
        $units = intval($data['units']);
        $room_id = isset($data['room_id']) && $data['room_id'] !== '' ? intval($data['room_id']) : null;

        $db->begin_transaction();
        try {
            $query = "UPDATE subjects SET code='$code', name='$name', units=$units WHERE id=$id";
            $db->query($query);

            // Clean up standalone room assignments mapping only to this subject
            $db->query("DELETE FROM schedules WHERE subject_id = $id AND faculty_id IS NULL");
            if ($room_id) {
                // If the user selects a room, assign it
                $db->query("INSERT INTO schedules (subject_id, room_id) VALUES ($id, $room_id)");
            }

            $db->commit();
            echo json_encode(["success" => true]);
        }
        catch (Exception $e) {
            $db->rollback();
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM subjects WHERE id = $id")) {
            echo json_encode(["success" => true]);
        }
        else {
            echo json_encode(["success" => false]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
