<?php
session_start();
// Require admin role
if (!isset($_SESSION['user_id'])) {
    header('Location: /PetResQ/login.php');
    exit;
}
$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
    http_response_code(403);
    echo 'Forbidden: Admins only';
    exit;
}
?>
<?php
// Serve the existing admin panel HTML
// You can later merge this into PHP if needed.
readfile(__DIR__ . '/index.php');
?>
