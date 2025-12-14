<?php
// Start output buffering to prevent any stray output
ob_start();

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Clean any output before JSON
ob_clean();

// Authz: admin only
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}
$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Forbidden']);
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

// First, get the submission details to find hewan_id
// Only select hewan_id (not id_hewan) as that's the correct column name in adoption_applications
$getSubmission = mysqli_prepare($conn, 'SELECT hewan_id FROM adoption_applications WHERE id = ?');
if (!$getSubmission) {
  http_response_code(500);
  ob_clean();
  $error = mysqli_error($conn);
  error_log("Failed to prepare query to get submission: " . $error);
  echo json_encode(['success' => false, 'message' => 'DB error (get submission): ' . $error]);
  exit;
}
mysqli_stmt_bind_param($getSubmission, 'i', $id);
if (!mysqli_stmt_execute($getSubmission)) {
  $error = mysqli_error($conn);
  mysqli_stmt_close($getSubmission);
  http_response_code(500);
  ob_clean();
  error_log("Failed to execute query to get submission: " . $error);
  echo json_encode(['success' => false, 'message' => 'DB error (execute get submission): ' . $error]);
  exit;
}
$submissionResult = mysqli_stmt_get_result($getSubmission);
$submission = mysqli_fetch_assoc($submissionResult);
mysqli_stmt_close($getSubmission);

if (!$submission) {
  http_response_code(404);
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Submission not found']);
  exit;
}

// Get hewan_id from submission
$hewan_id = 0;
if (isset($submission['hewan_id']) && $submission['hewan_id'] !== null) {
  $hewan_id = (int)$submission['hewan_id'];
}

// Validate hewan_id
if ($action === 'approve' && (!$hewan_id || $hewan_id <= 0)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid animal ID in submission']);
  exit;
}

// Update the current submission
$stmt = mysqli_prepare($conn, 'UPDATE adoption_applications SET status = ?, updated_at = NOW() WHERE id = ?');
if (!$stmt) {
  http_response_code(500);
  ob_clean();
  $error = mysqli_error($conn);
  error_log("Failed to prepare update submission query: " . $error);
  echo json_encode(['success' => false, 'message' => 'DB error (update submission): ' . $error]);
  exit;
}
mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
if (!mysqli_stmt_execute($stmt)) {
  $error = mysqli_error($conn);
  mysqli_stmt_close($stmt);
  http_response_code(500);
  ob_clean();
  error_log("Failed to execute update submission query: " . $error);
  echo json_encode(['success' => false, 'message' => 'DB error (execute update submission): ' . $error]);
  exit;
}
$submissionUpdated = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$submissionUpdated) {
  http_response_code(404);
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Submission not found or already updated']);
  exit;
}

// If approved, reject all other pending/in_review submissions for the same animal and update animal status
if ($action === 'approve' && $hewan_id > 0) {
  error_log("Approving submission #$id for animal id_hewan: $hewan_id");
  
  // Reject all other submissions for this animal (except the one just approved)
  $rejectOthers = mysqli_prepare($conn, 
    "UPDATE adoption_applications 
     SET status = 'rejected', updated_at = NOW() 
     WHERE hewan_id = ? 
     AND id != ? 
     AND status IN ('submitted', 'in_review', 'pending', 'Pending')"
  );
  if ($rejectOthers) {
    mysqli_stmt_bind_param($rejectOthers, 'ii', $hewan_id, $id);
    mysqli_stmt_execute($rejectOthers);
    mysqli_stmt_close($rejectOthers);
  }
  
  // Update animal status to "Adopted" - CRITICAL: This must happen when approval happens
  error_log("Attempting to update hewan id_hewan: $hewan_id to status 'Adopted'");
  
  // First, check if animal exists
  $checkAnimal = mysqli_prepare($conn, "SELECT id_hewan, status, namaHewan FROM hewan WHERE id_hewan = ?");
  if ($checkAnimal) {
    mysqli_stmt_bind_param($checkAnimal, 'i', $hewan_id);
    mysqli_stmt_execute($checkAnimal);
    $checkResult = mysqli_stmt_get_result($checkAnimal);
    $animalData = mysqli_fetch_assoc($checkResult);
    mysqli_stmt_close($checkAnimal);
    
    if (!$animalData) {
      error_log("ERROR: Animal with id_hewan: $hewan_id does not exist in hewan table!");
      ob_clean();
      echo json_encode([
        'success' => true, 
        'status' => $newStatus,
        'warning' => "Submission approved but animal (id_hewan: $hewan_id) not found in database"
      ]);
      exit;
    }
    
    error_log("Animal found: id_hewan={$animalData['id_hewan']}, namaHewan={$animalData['namaHewan']}, current_status={$animalData['status']}");
  }
  
  // Now update the animal status
  // Note: Check if updated_at column exists, if not, just update status
  $updateAnimal = mysqli_prepare($conn, 
    "UPDATE hewan SET status = 'Adopted' WHERE id_hewan = ?"
  );
  
  if (!$updateAnimal) {
    $prepareError = mysqli_error($conn);
    error_log("ERROR: Failed to prepare update statement for animal status: " . $prepareError);
    ob_clean();
    echo json_encode([
      'success' => true, 
      'status' => $newStatus,
      'warning' => 'Submission approved but failed to prepare animal status update: ' . $prepareError
    ]);
    exit;
  }
  
  mysqli_stmt_bind_param($updateAnimal, 'i', $hewan_id);
  $updateResult = mysqli_stmt_execute($updateAnimal);
  $affectedRows = mysqli_stmt_affected_rows($updateAnimal);
  $updateError = mysqli_error($conn);
  mysqli_stmt_close($updateAnimal);
  
  if (!$updateResult) {
    error_log("ERROR: Failed to execute update animal status query for hewan_id: $hewan_id. Error: " . $updateError);
    ob_clean();
    echo json_encode([
      'success' => true, 
      'status' => $newStatus,
      'warning' => 'Submission approved but failed to update animal status: ' . $updateError
    ]);
    exit;
  }
  
  if ($affectedRows === 0) {
    error_log("WARNING: No rows affected when updating animal status to Adopted for hewan_id: $hewan_id. Current status may already be 'Adopted'.");
  } else {
    error_log("SUCCESS: Updated animal id_hewan: $hewan_id to status 'Adopted'. Affected rows: $affectedRows");
  }
} else {
  if ($action === 'approve') {
    error_log("WARNING: Cannot update animal status - hewan_id is invalid: " . ($hewan_id ?? 'NULL'));
  }
}

// Clean output before sending JSON
ob_clean();
$response = ['success' => true, 'status' => $newStatus];
if ($action === 'approve' && $hewan_id > 0) {
  $response['hewan_id'] = $hewan_id;
  $response['message'] = 'Submission approved and animal status updated to Adopted';
}
echo json_encode($response);
exit;
?>
