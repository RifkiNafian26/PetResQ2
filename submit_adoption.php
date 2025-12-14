<?php
// Start output buffering
ob_start();

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    ob_clean();
    echo json_encode([ 'ok' => false, 'error' => 'Not authenticated' ]);
    exit;
}

require_once __DIR__ . '/config.php';

// Accept JSON or form-encoded payloads
$contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
$raw = file_get_contents('php://input');
$data = null;
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode($raw, true);
}
// Fallback to POST if JSON not provided or decode failed
if (!is_array($data) || empty($data)) {
    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        // Provide clearer diagnostic information
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Invalid payload: expected application/json or form data',
        ]);
        exit;
    }
}

// Basic required fields from SistemAdopt form
$hewan_id = null;
// Accept both 'hewan_id' and 'id_hewan' from payload
if (isset($data['hewan_id'])) {
    $hewan_id = (int)$data['hewan_id'];
} elseif (isset($data['id_hewan'])) {
    $hewan_id = (int)$data['id_hewan'];
}

// Validate hewan_id exists in database
if ($hewan_id === null || $hewan_id <= 0) {
    http_response_code(400);
    echo json_encode([ 'ok' => false, 'error' => 'Invalid or missing pet ID' ]);
    exit;
}

// Verify hewan exists and is available
$hewan_check = "SELECT id_hewan, status FROM hewan WHERE id_hewan = ?";
$hewan_stmt = mysqli_prepare($conn, $hewan_check);
if ($hewan_stmt) {
    mysqli_stmt_bind_param($hewan_stmt, 'i', $hewan_id);
    mysqli_stmt_execute($hewan_stmt);
    $hewan_result = mysqli_stmt_get_result($hewan_stmt);
    $hewan = mysqli_fetch_assoc($hewan_result);
    mysqli_stmt_close($hewan_stmt);
    
    if (!$hewan) {
        http_response_code(400);
        echo json_encode([ 'ok' => false, 'error' => 'Pet not found' ]);
        exit;
    }
    
    if ($hewan['status'] !== 'Available') {
        http_response_code(400);
        $statusMessage = $hewan['status'] === 'Adopted' 
            ? 'This pet has already been adopted by someone else.' 
            : 'Pet is not available for adoption';
        echo json_encode([ 'ok' => false, 'error' => $statusMessage ]);
        exit;
    }
}

$address = trim($data['address'] ?? '');
$postcode = trim($data['postcode'] ?? '');
$telephone = trim($data['telephone'] ?? '');
$garden = trim($data['garden'] ?? ''); // Yes/No
$living = trim($data['living_situation'] ?? '');
$household_setting = trim($data['household_setting'] ?? '');
$household_activity = trim($data['household_activity'] ?? '');
$adults = isset($data['adults']) ? (int)$data['adults'] : null;
$allergies = trim($data['allergies'] ?? '');
$other_animals = trim($data['other_animals'] ?? '');
$vaccinated = trim($data['vaccinated'] ?? '');
$experience = trim($data['experience'] ?? '');

if ($address === '' || $postcode === '' || $telephone === '' || $garden === '' ||
    $living === '' || $household_setting === '' || $household_activity === '' ||
    ($adults === null || $adults <= 0) || $allergies === '' || $other_animals === '' || $vaccinated === '') {
    http_response_code(400);
    echo json_encode([ 'ok' => false, 'error' => 'Missing required fields' ]);
    exit;
}

$applicant_user_id = (int)$_SESSION['user_id'];
$full_name = $_SESSION['user_name'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$has_garden = (strtolower($garden) === 'yes') ? 1 : 0;

// Handle home photos upload (if sent as base64 or file paths)
$home_photos = [];
if (isset($data['home_photos']) && is_array($data['home_photos']) && !empty($data['home_photos'])) {
    // If photos are sent as base64, save them
    $upload_dir = __DIR__ . '/uploads/adoption/home_photos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            http_response_code(500);
            ob_clean();
            echo json_encode([ 'ok' => false, 'error' => 'Failed to create upload directory' ]);
            exit;
        }
        chmod($upload_dir, 0777);
    }
    
    $user_id = (int)$_SESSION['user_id'];
    foreach ($data['home_photos'] as $index => $photo_data) {
        if (empty($photo_data)) continue;
        
        // Check if it's base64 data
        if (preg_match('/^data:image\/(\w+);base64,/', $photo_data, $matches)) {
            $image_type = strtolower($matches[1]);
            $image_data = base64_decode(substr($photo_data, strpos($photo_data, ',') + 1));
            
            // Validate file size (max 5MB, no minimum requirement)
            $file_size = strlen($image_data);
            if ($file_size > 0 && $file_size <= 5 * 1024 * 1024) { // Max 5MB
                $allowed_types = ['jpg', 'jpeg', 'png'];
                if (in_array($image_type, $allowed_types)) {
                    $filename = 'home_' . $user_id . '_' . time() . '_' . $index . '.' . $image_type;
                    $filepath = $upload_dir . $filename;
                    
                    if (file_put_contents($filepath, $image_data)) {
                        chmod($filepath, 0644);
                        $home_photos[] = 'uploads/adoption/home_photos/' . $filename;
                    } else {
                        error_log("Failed to save photo: $filepath");
                    }
                } else {
                    error_log("Invalid image type: $image_type");
                }
            } else {
                error_log("Invalid file size: $file_size bytes (max 5MB)");
            }
        } elseif (is_string($photo_data) && !empty($photo_data)) {
            // If it's already a path, use it directly
            $home_photos[] = $photo_data;
        }
    }
} else {
    // Log if home_photos is missing or empty
    if (!isset($data['home_photos'])) {
        error_log("home_photos key not found in data");
    } elseif (empty($data['home_photos'])) {
        error_log("home_photos array is empty");
    }
}

// Bundle all extra details to JSON for flexibility (including home_photos)
$details = [
    'telephone' => $telephone,
    'household_setting' => $household_setting,
    'household_activity' => $household_activity,
    'adults' => $adults,
    'children' => (int)($data['children'] ?? 0),
    'children_ages' => ($data['children_ages'] ?? null),
    'visiting_children' => ($data['visiting_children'] ?? null),
    'visiting_ages' => ($data['visiting_ages'] ?? null),
    'flatmates' => ($data['flatmates'] ?? null),
    'flatmates_consent' => ($data['flatmates_consent'] ?? null),
    'allergies' => $allergies,
    'other_animals' => $other_animals,
    'vaccinated' => $vaccinated,
    'experience' => $experience
];
$details_json = json_encode($details, JSON_UNESCAPED_UNICODE);
// Note: home_photos disimpan di kolom terpisah home_photos_json, bukan di details_json

// Insert application (now with hewan_id)
// Count: 14 columns, 14 placeholders (status is included, submitted_at and updated_at use NOW())
$sql = "INSERT INTO adoption_applications
    (applicant_user_id, assigned_admin_user_id, hewan_id, full_name, email, phone,
     address_line1, postcode, has_garden, living_situation, story, details_json, home_photos_json, status, submitted_at, updated_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";

$stmt = mysqli_prepare($conn, $sql);
$story = $experience; // reuse
$home_photos_json = json_encode($home_photos, JSON_UNESCAPED_UNICODE); // Array of photo paths
// Determine admin recipients dynamically (notify ALL admins)
$adminIds = [];
$adminLookup = mysqli_query($conn, "SELECT id_user FROM `user` WHERE role='admin' ORDER BY id_user ASC");
if ($adminLookup) {
    while ($r = mysqli_fetch_assoc($adminLookup)) {
        if (!empty($r['id_user'])) $adminIds[] = (int)$r['id_user'];
    }
}
if (empty($adminIds)) {
    // Fallback if no admin role found
    $adminIds[] = 1;
}
if (!$stmt) {
    http_response_code(500);
    ob_clean();
    echo json_encode([ 'ok' => false, 'error' => 'DB prepare failed', 'details' => mysqli_error($conn) ]);
    exit;
}
// Pick an assigned admin (or leave NULL) — also notify all admins separately
$assignedAdmin = isset($adminIds[0]) ? (int)$adminIds[0] : null;

$status = 'submitted';

mysqli_stmt_bind_param(
    $stmt,
    'iiissssissssss',
    $applicant_user_id,
    $assignedAdmin,
    $hewan_id,
    $full_name,
    $email,
    $telephone,
    $address,
    $postcode,
    $has_garden,
    $living,
    $story,
    $details_json,
    $home_photos_json,
    $status
);

if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    ob_clean();
    echo json_encode([ 'ok' => false, 'error' => 'DB insert failed', 'details' => mysqli_error($conn) ]);
    exit;
}

$appId = mysqli_insert_id($conn);

// Create a notification for admin id=1
$noteSql = "INSERT INTO notifications (recipient_user_id, application_id, message) VALUES (?, ?, ?)";
$message = 'New adoption application from ' . ($full_name ?: 'Unknown');
foreach ($adminIds as $aid) {
    $noteStmt = mysqli_prepare($conn, $noteSql);
    if ($noteStmt) {
        mysqli_stmt_bind_param($noteStmt, 'iis', $aid, $appId, $message);
        if (!mysqli_stmt_execute($noteStmt)) {
            $noteError = (isset($noteError) ? $noteError.' | ' : '') . mysqli_error($conn);
        }
        mysqli_stmt_close($noteStmt);
    } else {
        $noteError = (isset($noteError) ? $noteError.' | ' : '') . 'prepare failed';
    }
}

mysqli_stmt_close($stmt);

$resp = [ 'ok' => true, 'application_id' => $appId ];
if (isset($noteError)) {
    $resp['notification_warning'] = $noteError;
}

ob_clean();
echo json_encode($resp);
exit;
