<?php
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $result = $db->query("SELECT * FROM rooms ORDER BY name ASC");
        $rooms = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rooms);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $db->real_escape_string($data['name']);
        $capacity = intval($data['capacity']);
        $type = $db->real_escape_string($data['type']);

        $query = "INSERT INTO rooms (name, capacity, type) VALUES ('$name', $capacity, '$type')";
        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        }
        else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM rooms WHERE id = $id")) {
            echo json_encode(["success" => true]);
        }
        else {
            echo json_encode(["success" => false]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $db->real_escape_string($data['name']);
        $type = $db->real_escape_string($data['type']);

        $query = "UPDATE rooms SET name = '$name', type = '$type' WHERE id = $id";
        if ($db->query($query)) {
            echo json_encode(["success" => true]);
        }
        else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
