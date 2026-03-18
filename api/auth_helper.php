<?php
/**
 * auth_helper.php
 * Include this in any API file that needs user-isolation.
 * Returns the currently logged-in user ID or sends a 401 and exits.
 */
function requireUserId(): int {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthenticated"]);
        exit;
    }
    return (int)$_SESSION['user_id'];
}
