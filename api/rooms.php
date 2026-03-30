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
        $result = $db->query("SELECT * FROM rooms WHERE user_id = $uid OR user_id IS NULL ORDER BY name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $db->real_escape_string($data['name']);
        $capacity = intval($data['capacity']);
        $type = $db->real_escape_string($data['type']);

        $query = "INSERT INTO rooms (name, capacity, type, user_id) VALUES ('$name', $capacity, '$type', $uid)";
        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        } else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM rooms WHERE id = $id AND user_id = $uid")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents("php://input"), true);

        // Ownership check
        $own = $db->query("SELECT id FROM rooms WHERE id = $id AND user_id = $uid");
        if ($own->num_rows === 0) { echo json_encode(["success" => false, "error" => "Not found"]); break; }

        $name = $db->real_escape_string($data['name']);
        $type = $db->real_escape_string($data['type']);

        $query = "UPDATE rooms SET name = '$name', type = '$type' WHERE id = $id AND user_id = $uid";
        if ($db->query($query)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
}
?>
