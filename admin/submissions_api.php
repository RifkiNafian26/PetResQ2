<?php
session_start();
header('Content-Type: application/json');
// Ensure PHP notices don't break JSON output
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/../config.php';

// Require admin login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Fetch adoption submissions with useful joins
$sql = "SELECT id,
        full_name AS adopter_name,
        email AS adopter_email,
        phone AS adopter_phone,
        story AS adopter_reason,
        status AS app_status,
        submitted_at,
        hewan_id,
        address_line1,
        postcode,
        has_garden,
        living_situation,
        details_json,
        home_photos_json
    FROM adoption_applications
    ORDER BY submitted_at DESC
    LIMIT 200";

$res = mysqli_query($conn, $sql);
$items = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        // Normalize status to title-case values used in UI
        $status = isset($row['app_status']) ? strtolower($row['app_status']) : 'submitted';
        if ($status === 'submitted') $statusOut = 'Pending';
        elseif ($status === 'in_review') $statusOut = 'Pending';
        elseif ($status === 'approved') $statusOut = 'Approved';
        elseif ($status === 'rejected') $statusOut = 'Rejected';
        else $statusOut = ucfirst($status);

        // Parse additional details JSON if present
        $extra = [];
        if (!empty($row['details_json'])) {
            $decoded = json_decode($row['details_json'], true);
            if (is_array($decoded)) {
                $extra = $decoded;
            }
        }

        // Parse home photos JSON if present
        $home_photos = [];
        if (!empty($row['home_photos_json'])) {
            $photos_decoded = json_decode($row['home_photos_json'], true);
            if (is_array($photos_decoded)) {
                $home_photos = $photos_decoded;
            }
        }

        $items[] = [
            'id' => isset($row['id']) ? (int)$row['id'] : 0,
            'adopterName' => $row['adopter_name'] ?? ($row['full_name'] ?? ''),
            'adopterEmail' => $row['adopter_email'] ?? ($row['email'] ?? ''),
            'adopterPhone' => $row['adopter_phone'] ?? ($row['phone'] ?? ''),
            'animalName' => isset($row['hewan_id']) ? 'Pet #' . (int)$row['hewan_id'] : 'Unknown',
            'date' => $row['submitted_at'] ?? '',
            'status' => $statusOut,
            'reason' => $row['adopter_reason'] ?? '',
            'address' => $row['address_line1'] ?? '',
            // 'city' column removed from schema; keep empty for compatibility
            'city' => '',
            'postcode' => $row['postcode'] ?? '',
            'hasGarden' => isset($row['has_garden']) ? (int)$row['has_garden'] : 0,
            'living' => $row['living_situation'] ?? '',
            'details' => $extra,
            'home_photos' => $home_photos // Array of photo paths
        ];
    }
    echo json_encode(['data' => $items]);
    exit;
} else {
    // Fallback: attempt minimal query to avoid hard failure
    $fallback = mysqli_query($conn, "SELECT id, full_name, email, status, submitted_at FROM adoption_applications ORDER BY submitted_at DESC LIMIT 200");
    if ($fallback) {
        while ($row = mysqli_fetch_assoc($fallback)) {
            $status = isset($row['status']) ? strtolower($row['status']) : 'submitted';
            $statusOut = ($status === 'approved') ? 'Approved' : (($status === 'rejected') ? 'Rejected' : 'Pending');
            $items[] = [
                'id' => isset($row['id']) ? (int)$row['id'] : 0,
                'adopterName' => $row['full_name'] ?? '',
                'adopterEmail' => $row['email'] ?? '',
                'adopterPhone' => '',
                'animalName' => 'Unknown',
                'date' => $row['submitted_at'] ?? '',
                'status' => $statusOut,
                'reason' => '',
                'address' => '',
                'city' => '',
                'postcode' => '',
                'hasGarden' => 0,
                'living' => '',
                'details' => []
            ];
        }
        echo json_encode(['data' => $items, 'warning' => 'Partial data; full query failed', 'details' => mysqli_error($conn)]);
        exit;
    }
    // As a last resort, return empty data with error detail instead of 500
    http_response_code(200);
    echo json_encode(['data' => [], 'error' => 'Query failed', 'details' => mysqli_error($conn)]);
    exit;
}
?>
