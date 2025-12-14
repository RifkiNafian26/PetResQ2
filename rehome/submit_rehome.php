<?php
// Start output buffering to prevent stray output breaking JSON
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', '../error_log.txt');

// Global JSON error/exception handlers
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    // Clear any buffered output
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Server error', 'detail' => $message]);
    exit;
});

set_exception_handler(function($e) {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Unhandled exception', 'detail' => $e->getMessage()]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'message' => 'Fatal error', 'detail' => $error['message']]);
    }
});

session_start();
require_once '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Check database connection
if (!isset($conn) || !$conn) {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {

// Get data from either JSON or FormData/POST
$data = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    // FormData from form submission
    $data = [
        'pet_name' => $_POST['pet_name'] ?? null,
        'pet_type' => $_POST['pet_type'] ?? null,
        'age_years' => $_POST['age_years'] ?? null,
        'breed' => $_POST['breed'] ?? null,
        'color' => $_POST['color'] ?? null,
        'weight' => $_POST['weight'] ?? null,
        'height' => $_POST['height'] ?? null,
        'gender' => $_POST['gender'] ?? null,
        'address_line1' => $_POST['address_line1'] ?? null,
        'city' => $_POST['city'] ?? null,
        'postcode' => $_POST['postcode'] ?? null,
        'spayed_neutered' => $_POST['spayed_neutered'] ?? null,
        'rehome_reason' => $_POST['rehome_reason'] ?? null,
        'pet_story' => $_POST['pet_story'] ?? null,
    ];
}

// Validate required fields
$required_fields = ['pet_name', 'pet_type', 'age_years', 'breed', 'color', 'weight', 'height', 'gender', 
                    'address_line1', 'city', 'postcode', 'spayed_neutered', 'rehome_reason', 'pet_story'];

$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
    exit;
}

// Handle file upload for pet image - REQUIRED
$pet_image_path = null;
$pet_image_required = true;

if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/rehome/';
    
    // Create directory if it doesn't exist with proper permissions
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            http_response_code(500);
            if (ob_get_level()) { ob_clean(); }
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit;
        }
        // Set permissions explicitly
        chmod($upload_dir, 0777);
    } else {
        // Ensure existing directory has write permissions
        if (!is_writable($upload_dir)) {
            chmod($upload_dir, 0777);
        }
    }
    
    $file_ext = strtolower(pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png'];
    
    if (in_array($file_ext, $allowed_exts)) {
        $file_size = $_FILES['pet_image']['size'];
        
        // Check file size (240 KB to 1024 KB)
        if ($file_size >= 240 * 1024 && $file_size <= 1024 * 1024) {
            $filename = 'pet_' . $user_id . '_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $filepath)) {
                $pet_image_path = 'uploads/rehome/' . $filename;
            }
        } else {
            http_response_code(400);
            if (ob_get_level()) { ob_clean(); }
            echo json_encode(['success' => false, 'message' => 'Pet image size must be between 240 KB and 1024 KB']);
            exit;
        }
    } else {
        http_response_code(400);
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'message' => 'Pet image must be in JPG, JPEG, or PNG format']);
        exit;
    }
} else {
    // Check if file was uploaded but had an error
    if (isset($_FILES['pet_image'])) {
        $error = $_FILES['pet_image']['error'];
        if ($error === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            if (ob_get_level()) { ob_clean(); }
            echo json_encode(['success' => false, 'message' => 'Pet image is required. Please upload a photo of your pet.']);
            exit;
        } else {
            http_response_code(400);
            if (ob_get_level()) { ob_clean(); }
            echo json_encode(['success' => false, 'message' => 'Error uploading pet image. Please try again.']);
            exit;
        }
    } else {
        http_response_code(400);
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'message' => 'Pet image is required. Please upload a photo of your pet.']);
        exit;
    }
}

// Validate that pet_image_path is set
if (empty($pet_image_path)) {
    http_response_code(400);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Pet image is required. Please upload a photo of your pet.']);
    exit;
}

// Handle multiple document uploads
$documents_json = null;
if (isset($_FILES['documents'])) {
    $documents = [];
    $doc_count = count($_FILES['documents']['name']);
    
    for ($i = 0; $i < $doc_count; $i++) {
            if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/rehome/documents/';
            
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    continue; // Skip this document if directory creation fails
                }
                chmod($upload_dir, 0777);
            } else {
                if (!is_writable($upload_dir)) {
                    chmod($upload_dir, 0777);
                }
            }
            
            $file_ext = strtolower(pathinfo($_FILES['documents']['name'][$i], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $file_size = $_FILES['documents']['size'][$i];
                
                if ($file_size >= 240 * 1024 && $file_size <= 1024 * 1024) {
                    $filename = 'doc_' . $user_id . '_' . time() . '_' . $i . '.' . $file_ext;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $filepath)) {
                        $documents[] = 'uploads/rehome/documents/' . $filename;
                    }
                }
            }
        }
    }
    
    if (!empty($documents)) {
        $documents_json = json_encode($documents);
    }
}

// Get admin user ID first (any user with role='admin')
$admin_id = 1;
$admin_query = "SELECT id_user FROM user WHERE role='admin' ORDER BY id_user ASC LIMIT 1";
$admin_result = mysqli_query($conn, $admin_query);
if ($admin_result) {
    $admin_user = mysqli_fetch_assoc($admin_result);
    if ($admin_user) {
        $admin_id = $admin_user['id_user'];
    }
}

// Prepare and execute insert query
$query = "INSERT INTO rehome_submissions (
    user_id, assigned_admin_user_id,
    pet_name, pet_type, age_years, breed, color, weight, height, gender,
    address_line1, city, postcode,
    spayed_neutered, rehome_reason, pet_story,
    pet_image_path, documents_json, status, submitted_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

// Prepare variables for binding
$age_years = intval($data['age_years']);
$weight = floatval($data['weight']);
$height = floatval($data['height']);
$status = 'submitted';

// Type string: i=int, s=string, d=double
// user_id(i), admin_id(i), pet_name(s), pet_type(s), age_years(i), breed(s), color(s), weight(d), height(d), 
// gender(s), address_line1(s), city(s), postcode(s), spayed_neutered(s), rehome_reason(s), pet_story(s), 
// pet_image_path(s), documents_json(s), status(s)
$bind_types = "iississddssssssssss";

// Assign to local variables (required: bind_param uses references)
$pet_name = $data['pet_name'];
$pet_type = $data['pet_type'];
$breed = $data['breed'];
$color = $data['color'];
$gender = $data['gender'];
$address_line1 = $data['address_line1'];
$city = $data['city'];
$postcode = $data['postcode'];
$spayed_neutered = $data['spayed_neutered'];
$rehome_reason = $data['rehome_reason'];
$pet_story = $data['pet_story'];

mysqli_stmt_bind_param(
    $stmt,
    $bind_types,
    $user_id,
    $admin_id,
    $pet_name,
    $pet_type,
    $age_years,
    $breed,
    $color,
    $weight,
    $height,
    $gender,
    $address_line1,
    $city,
    $postcode,
    $spayed_neutered,
    $rehome_reason,
    $pet_story,
    $pet_image_path,
    $documents_json,
    $status
);

// Defensive check: ensure counts match
$placeholder_count = substr_count($query, '?');
$types_len = strlen($bind_types);
$vars_count = 19;
if ($placeholder_count !== $types_len || $types_len !== $vars_count) {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode([
        'success' => false,
        'message' => 'Binding mismatch',
        'detail' => "placeholders=$placeholder_count types=$types_len vars=$vars_count"
    ]);
    exit;
}

if (mysqli_stmt_execute($stmt)) {
    $submission_id = mysqli_insert_id($conn);
    
    // Create notification for admin
    $notif_query = "INSERT INTO notifications (recipient_user_id, application_id, message) 
                    VALUES (?, NULL, ?)";
    $notif_stmt = mysqli_prepare($conn, $notif_query);
    $message = "New rehome submission from " . htmlspecialchars($data['pet_name'] ?? 'Unknown');
    mysqli_stmt_bind_param($notif_stmt, "is", $admin_id, $message);
    mysqli_stmt_execute($notif_stmt);
    
    http_response_code(201);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => true, 'message' => 'Submission saved successfully', 'submission_id' => $submission_id]);
} else {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Error saving submission: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

// Ensure buffer is clean and flushed just once
if (ob_get_level()) { ob_end_flush(); }
} catch (Throwable $e) {
    http_response_code(500);
    if (ob_get_level()) { ob_clean(); }
    echo json_encode([
        'success' => false,
        'message' => 'Unhandled exception',
        'detail' => $e->getMessage()
    ]);
}
?>
