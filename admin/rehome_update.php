<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

// Check if admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Get data from JSON or form
$data = [];
$contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';

if (strpos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
} else {
    $data = $_POST;
    if (empty($data)) {
        $data = $_GET;
    }
}

$id = isset($data['id']) ? intval($data['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
$status = isset($data['status']) ? $data['status'] : (isset($_GET['status']) ? $_GET['status'] : null);
$notes = isset($data['notes']) ? $data['notes'] : (isset($_POST['notes']) ? $_POST['notes'] : null);

if ($id === 0 || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Validate status
$valid_statuses = ['submitted', 'in_review', 'approved', 'rejected', 'withdrawn'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Update status
$query = "UPDATE rehome_submissions SET status = ?, admin_notes = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssi", $status, $notes, $id);

if (mysqli_stmt_execute($stmt)) {
    // If status is approved, create notification
    if ($status === 'approved') {
        // Get user_id from submission
        $user_query = "SELECT user_id FROM rehome_submissions WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, "i", $id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user_row = mysqli_fetch_assoc($user_result)) {
            $notif_query = "INSERT INTO notifications (recipient_user_id, application_id, message) VALUES (?, NULL, ?)";
            $notif_stmt = mysqli_prepare($conn, $notif_query);
            $message = "Your rehome submission #" . $id . " has been approved!";
            mysqli_stmt_bind_param($notif_stmt, "is", $user_row['user_id'], $message);
            mysqli_stmt_execute($notif_stmt);
            mysqli_stmt_close($notif_stmt);
        }
        mysqli_stmt_close($user_stmt);
    }
    
    // Update updated_at timestamp
    $update_time_query = "UPDATE rehome_submissions SET updated_at = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_time_query);
    mysqli_stmt_bind_param($update_stmt, "i", $id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    // Return JSON response
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    exit;
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
