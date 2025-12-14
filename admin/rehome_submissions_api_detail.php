<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/../config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Require admin role
$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$sql = "SELECT rs.*, u.nama, u.email
        FROM rehome_submissions rs
        LEFT JOIN user u ON rs.user_id = u.id_user
        WHERE rs.id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$submission = mysqli_fetch_assoc($result);

if (!$submission) {
    http_response_code(404);
    echo json_encode(['error' => 'Submission not found']);
    exit;
}

// Decode documents JSON
$documents = [];
if (!empty($submission['documents_json'])) {
    $documents = json_decode($submission['documents_json'], true) ?? [];
}

$response = [
    'id' => (int)$submission['id'],
    'user_id' => (int)$submission['user_id'],
    'owner_name' => $submission['nama'] ?? 'Unknown',
    'owner_email' => $submission['email'] ?? 'N/A',
    'pet_name' => $submission['pet_name'],
    'pet_type' => $submission['pet_type'],
    'age_years' => (int)$submission['age_years'],
    'breed' => $submission['breed'],
    'color' => $submission['color'],
    'weight' => (float)$submission['weight'],
    'height' => (float)$submission['height'],
    'gender' => $submission['gender'],
    'address_line1' => $submission['address_line1'],
    'city' => $submission['city'],
    'postcode' => $submission['postcode'],
    'spayed_neutered' => $submission['spayed_neutered'],
    'rehome_reason' => $submission['rehome_reason'],
    'pet_story' => $submission['pet_story'],
    'pet_image_path' => $submission['pet_image_path'] ?? null,
    'documents' => $documents,
    'status' => $submission['status'],
    'submitted_at' => $submission['submitted_at'],
    'updated_at' => $submission['updated_at'] ?? $submission['submitted_at'],
    'admin_notes' => $submission['admin_notes'] ?? null
];

echo json_encode($response);
?>

