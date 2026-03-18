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
        $result = $db->query("SELECT * FROM faculty WHERE user_id = $uid ORDER BY name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $db->real_escape_string($data['name'] ?? '');
        $department = $db->real_escape_string($data['department'] ?? 'IT');
        $email = $db->real_escape_string($data['email'] ?? '');
        $status = $db->real_escape_string($data['status'] ?? 'Active');
        $employment_status = $db->real_escape_string($data['employment_status'] ?? 'Full-time');
        $designated_campus = $db->real_escape_string($data['designated_campus'] ?? 'Main Campus');

        $query = "INSERT INTO faculty (name, department, email, status, employment_status, designated_campus, user_id)
                  VALUES ('$name', '$department', '$email', '$status', '$employment_status', '$designated_campus', $uid)";
        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        } else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents("php://input"), true);

        // Ownership check
        $own = $db->query("SELECT id FROM faculty WHERE id = $id AND user_id = $uid");
        if ($own->num_rows === 0) { echo json_encode(["success" => false, "error" => "Not found"]); break; }

        $name = isset($data['name']) ? $db->real_escape_string($data['name']) : null;
        $employment_status = isset($data['employment_status']) ? $db->real_escape_string($data['employment_status']) : null;

        if ($id > 0 && ($employment_status !== null || $name !== null)) {
            $updates = [];
            if ($name !== null) $updates[] = "name = '$name'";
            if ($employment_status !== null && $employment_status !== '') $updates[] = "employment_status = '$employment_status'";
            if (isset($data['designated_campus'])) {
                $campus = $db->real_escape_string($data['designated_campus']);
                $updates[] = "designated_campus = '$campus'";
            }
            if (!empty($updates)) {
                $updateSql = implode(', ', $updates);
                if ($db->query("UPDATE faculty SET $updateSql WHERE id = $id AND user_id = $uid")) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "error" => $db->error]);
                }
            } else {
                echo json_encode(["success" => true]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Invalid data"]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM faculty WHERE id = $id AND user_id = $uid")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
        break;

    default:
        echo json_encode(["error" => "Method not allowed"]);
}
?>
