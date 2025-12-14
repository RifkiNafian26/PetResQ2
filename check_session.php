<?php
// Prevent any output before JSON
ob_start();

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Clear any output buffer
ob_clean();

$response = [
    'is_logged_in' => false,
    'user_name' => '',
    'user_email' => '',
    'user_id' => null,
    'role' => null
];

if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    $response['is_logged_in'] = true;
    $response['user_id'] = $_SESSION['user_id'];
    $response['user_name'] = $_SESSION['user_name'] ?? '';
    $response['user_email'] = $_SESSION['user_email'] ?? '';
    if (isset($_SESSION['role'])) {
        $response['role'] = $_SESSION['role'];
    }
}

echo json_encode($response);
exit;
?>
