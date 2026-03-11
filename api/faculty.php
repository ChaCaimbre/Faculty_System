<?php
header("Content-Type: application/json");
require_once '../config.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $result = $db->query("SELECT * FROM faculty ORDER BY name ASC");
        $faculty = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($faculty);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $db->real_escape_string($data['name'] ?? '');
        $department = $db->real_escape_string($data['department'] ?? 'IT');
        $email = $db->real_escape_string($data['email'] ?? '');
        $status = $db->real_escape_string($data['status'] ?? 'Active');
        $employment_status = $db->real_escape_string($data['employment_status'] ?? 'Full-time');
        $designated_campus = $db->real_escape_string($data['designated_campus'] ?? 'Main Campus');

        $query = "INSERT INTO faculty (name, department, email, status, employment_status, designated_campus) VALUES ('$name', '$department', '$email', '$status', '$employment_status', '$designated_campus')";
        if ($db->query($query)) {
            echo json_encode(["success" => true, "id" => $db->insert_id]);
        }
        else {
            echo json_encode(["success" => false, "error" => $db->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents("php://input"), true);
        $name = isset($data['name']) ? $db->real_escape_string($data['name']) : null;
        $employment_status = isset($data['employment_status']) ? $db->real_escape_string($data['employment_status']) : null;

        if ($id > 0 && ($employment_status !== null || $name !== null)) {
            $updates = [];
            if ($name !== null) {
                $updates[] = "name = '$name'";
            }
            if ($employment_status !== null && $employment_status !== '') {
                $updates[] = "employment_status = '$employment_status'";
            }
            if (isset($data['designated_campus'])) {
                $campus = $db->real_escape_string($data['designated_campus']);
                $updates[] = "designated_campus = '$campus'";
            }

            if (!empty($updates)) {
                $updateSql = implode(', ', $updates);
                if ($db->query("UPDATE faculty SET $updateSql WHERE id = $id")) {
                    echo json_encode(["success" => true]);
                }
                else {
                    echo json_encode(["success" => false, "error" => $db->error]);
                }
            }
            else {
                echo json_encode(["success" => true]);
            }
        }
        else {
            echo json_encode(["success" => false, "error" => "Invalid data"]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        if ($db->query("DELETE FROM faculty WHERE id = $id")) {
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
