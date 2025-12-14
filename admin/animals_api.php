<?php
// Start output buffering
ob_start();

session_start();
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Clear any output before JSON
ob_clean();

require_once __DIR__ . '/../config.php';

// Check database connection
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Check if admin
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

// Get all animals with uploaded by info
// Check if rehome_submissions table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'rehome_submissions'");
$hasRehomeTable = mysqli_num_rows($checkTable) > 0;

if ($hasRehomeTable) {
    // Check if rehome_submission_id column exists in hewan table
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM hewan LIKE 'rehome_submission_id'");
    $hasRehomeColumn = mysqli_num_rows($checkColumn) > 0;
    
    if ($hasRehomeColumn) {
        $query = "SELECT 
            h.*,
            u.nama as uploaded_by_name
        FROM hewan h
        LEFT JOIN rehome_submissions rs ON h.rehome_submission_id = rs.id
        LEFT JOIN user u ON rs.user_id = u.id_user
        ORDER BY h.id_hewan DESC";
    } else {
        // If rehome_submission_id column doesn't exist, just get animals
        $query = "SELECT 
            h.*,
            'System' as uploaded_by_name
        FROM hewan h
        ORDER BY h.id_hewan DESC";
    }
} else {
    // If rehome_submissions table doesn't exist, just get animals
    $query = "SELECT 
        h.*,
        'System' as uploaded_by_name
    FROM hewan h
    ORDER BY h.id_hewan DESC";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'error' => 'Database query failed',
        'message' => mysqli_error($conn),
        'query' => $query
    ]);
    exit;
}

$animals = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Determine uploaded by
    if (!empty($row['uploaded_by_name']) && $row['uploaded_by_name'] !== 'System') {
        $uploadedBy = $row['uploaded_by_name'];
    } else {
        $uploadedBy = 'System';
    }
    
    $animals[] = [
        'id_hewan' => (int)$row['id_hewan'],
        'namaHewan' => $row['namaHewan'] ?? '',
        'jenis' => $row['jenis'] ?? '',
        'breed' => $row['breed'] ?? '',
        'gender' => $row['gender'] ?? '',
        'age' => $row['age'] ?? '',
        'color' => $row['color'] ?? '',
        'weight' => isset($row['weight']) ? (float)$row['weight'] : 0,
        'height' => isset($row['height']) ? (float)$row['height'] : 0,
        'main_photo' => $row['main_photo'] ?? '',
        'description' => $row['description'] ?? '',
        'status' => $row['status'] ?? 'Available',
        'uploaded_by' => $uploadedBy
    ];
}

// Get summary counts
$totalAnimals = count($animals);
$totalDogs = count(array_filter($animals, function($a) { return strtolower($a['jenis']) === 'dog'; }));
$totalCats = count(array_filter($animals, function($a) { return strtolower($a['jenis']) === 'cat'; }));
$totalRabbits = count(array_filter($animals, function($a) { return strtolower($a['jenis']) === 'rabbit'; }));

$response = [
    'animals' => $animals,
    'summary' => [
        'total' => $totalAnimals,
        'dogs' => $totalDogs,
        'cats' => $totalCats,
        'rabbits' => $totalRabbits
    ]
];

// Clear any output before sending JSON
ob_clean();
echo json_encode($response);
exit;
?>

