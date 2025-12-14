<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

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

// Get data from JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$submission_id = isset($data['submission_id']) ? (int)$data['submission_id'] : 0;
$pet_name = $data['pet_name'] ?? '';
$pet_type = $data['pet_type'] ?? '';
$breed = $data['breed'] ?? '';
$gender = $data['gender'] ?? '';
$age = $data['age'] ?? '';
$color = $data['color'] ?? '';
$weight = $data['weight'] ?? '';
$height = $data['height'] ?? '';
$main_photo = $data['main_photo'] ?? null;
$description = $data['description'] ?? '';

// Validate required fields
if (empty($pet_name) || empty($pet_type) || empty($breed) || empty($gender)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check if animal already exists from this submission
$check_query = "SELECT id_hewan FROM hewan WHERE main_photo = ? AND namaHewan = ? LIMIT 1";
$check_stmt = mysqli_prepare($conn, $check_query);
if ($check_stmt && $main_photo) {
    mysqli_stmt_bind_param($check_stmt, "ss", $main_photo, $pet_name);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    if (mysqli_num_rows($check_result) > 0) {
        mysqli_stmt_close($check_stmt);
        echo json_encode(['success' => false, 'message' => 'Animal already exists from this submission']);
        exit;
    }
    mysqli_stmt_close($check_stmt);
}

// Insert into hewan table
$insert_query = "INSERT INTO hewan (jenis, namaHewan, breed, gender, age, color, weight, height, main_photo, description, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')";

$stmt = mysqli_prepare($conn, $insert_query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "ssssssddss", 
    $pet_type,
    $pet_name,
    $breed,
    $gender,
    $age,
    $color,
    $weight,
    $height,
    $main_photo,
    $description
);

if (mysqli_stmt_execute($stmt)) {
    $animal_id = mysqli_insert_id($conn);
    
    // Update rehome submission to link with animal
    if ($submission_id > 0) {
        $update_query = "UPDATE rehome_submissions SET admin_notes = CONCAT(COALESCE(admin_notes, ''), '\nAnimal created with ID: ', ?) WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "ii", $animal_id, $submission_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Animal created successfully',
        'animal_id' => $animal_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create animal: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

