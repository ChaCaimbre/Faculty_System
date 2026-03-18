<?php
header("Content-Type: application/json");
session_start();
require_once '../config.php';
require_once 'auth_helper.php';

$db = getDB();
$uid = requireUserId();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 1;
        $result = $db->query("SELECT s.*, c.name as curriculum_name 
                              FROM subjects s 
                              LEFT JOIN curricula c ON s.curriculum_id = c.id 
                              WHERE s.user_id = $uid AND s.term_id = $term_id 
                              ORDER BY s.code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $code = $db->real_escape_string($data['code']);
        $name = $db->real_escape_string($data['name']);
        $units = intval($data['units']);
        $term_id = isset($data['term_id']) ? intval($data['term_id']) : 1;
        $curriculum_id = isset($data['curriculum_id']) ? intval($data['curriculum_id']) : 1;
        $room_id = isset($data['room_id']) && $data['room_id'] !== '' ? intval($data['room_id']) : null;

        $db->begin_transaction();
        try {
            $query = "INSERT INTO subjects (code, name, units, user_id, term_id, curriculum_id) 
                      VALUES ('$code', '$name', $units, $uid, $term_id, $curriculum_id)";
            $db->query($query);
            $subject_id = $db->insert_id;

            if ($room_id) {
                $db->query("INSERT INTO schedules (subject_id, room_id, user_id) VALUES ($subject_id, $room_id, $uid)");
            }
            $db->commit();
            echo json_encode(["success" => true, "id" => $subject_id]);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents("php://input"), true);

        // Ownership check
        $own = $db->query("SELECT id FROM subjects WHERE id = $id AND user_id = $uid");
        if ($own->num_rows === 0) { echo json_encode(["success" => false, "error" => "Not found"]); break; }

        $code = $db->real_escape_string($data['code']);
        $name = $db->real_escape_string($data['name']);
        $units = intval($data['units']);
        $room_id = isset($data['room_id']) && $data['room_id'] !== '' ? intval($data['room_id']) : null;

        $db->begin_transaction();
        try {
            $db->query("UPDATE subjects SET code='$code', name='$name', units=$units WHERE id=$id AND user_id=$uid");
            $db->query("DELETE FROM schedules WHERE subject_id = $id AND faculty_id IS NULL AND user_id = $uid");
            if ($room_id) {
                $db->query("INSERT INTO schedules (subject_id, room_id, user_id) VALUES ($id, $room_id, $uid)");
            }
            $db->commit();
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM subjects WHERE id = $id AND user_id = $uid")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
}
?>
