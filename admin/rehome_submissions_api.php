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

$sql = "SELECT 
            rs.id,
            rs.user_id,
            rs.pet_name,
            rs.pet_type,
            rs.age_years,
            rs.breed,
            rs.gender,
            rs.city,
            rs.postcode,
            rs.spayed_neutered,
            rs.rehome_reason,
            rs.status,
            rs.submitted_at,
            u.nama,
            u.email
        FROM rehome_submissions rs
        LEFT JOIN user u ON rs.user_id = u.id_user
        ORDER BY rs.submitted_at DESC
        LIMIT 200";

$res = mysqli_query($conn, $sql);
$items = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $items[] = [
            'id' => (int)($row['id'] ?? 0),
            'user_id' => (int)($row['user_id'] ?? 0),
            'pet_name' => $row['pet_name'] ?? '',
            'pet_type' => $row['pet_type'] ?? '',
            'age_years' => isset($row['age_years']) ? (int)$row['age_years'] : null,
            'breed' => $row['breed'] ?? '',
            'gender' => $row['gender'] ?? '',
            'city' => $row['city'] ?? '',
            'postcode' => $row['postcode'] ?? '',
            'spayed_neutered' => isset($row['spayed_neutered']) ? (int)$row['spayed_neutered'] : null,
            'rehome_reason' => $row['rehome_reason'] ?? '',
            'status' => $row['status'] ?? 'submitted',
            'submitted_at' => $row['submitted_at'] ?? '',
            'nama' => $row['nama'] ?? '',
            'email' => $row['email'] ?? '',
        ];
    }
    echo json_encode(['data' => $items]);
    exit;
} else {
    // Return an empty list with error info to avoid breaking frontend
    http_response_code(200);
    echo json_encode(['data' => [], 'error' => 'Query failed', 'details' => mysqli_error($conn)]);
    exit;
}
?>
