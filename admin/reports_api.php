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

$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch reports
if ($method === 'GET') {
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    $sql = "SELECT r.id, r.report_type, r.start_date, r.end_date, r.description, 
                   r.status, r.created_at, r.updated_at, u.nama AS created_by_name
            FROM reports r
            LEFT JOIN user u ON r.created_by = u.id_user
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($date) {
        $sql .= " AND DATE(r.created_at) = ?";
        $params[] = $date;
        $types .= 's';
    }
    
    if ($start_date && $end_date) {
        $sql .= " AND r.start_date >= ? AND r.end_date <= ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= 'ss';
    }
    
    $sql .= " ORDER BY r.created_at DESC LIMIT 200";
    
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $sql);
    }
    
    $items = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => (int)$row['id'],
                'report_type' => $row['report_type'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'description' => $row['description'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'created_by_name' => $row['created_by_name'] ?? 'Unknown'
            ];
        }
    }
    
    echo json_encode(['data' => $items]);
    exit;
}

// POST - Create report
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $report_type = $data['report_type'] ?? 'Daily';
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $description = $data['description'] ?? '';
    $created_by = (int)$_SESSION['user_id'];
    
    // For Notes type, description can be empty
    if (!$start_date || !$end_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields: start_date and end_date are required']);
        exit;
    }
    
    // For non-Notes types, description is required
    if ($report_type !== 'Notes' && !$description) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Description is required for this report type']);
        exit;
    }
    
    // Validate report type
    if (!in_array($report_type, ['Daily', 'Weekly', 'Monthly', 'Notes'])) {
        $report_type = 'Daily';
    }
    
    $sql = "INSERT INTO reports (report_type, start_date, end_date, description, status, created_by) 
            VALUES (?, ?, ?, ?, 'pending', ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, 'ssssi', $report_type, $start_date, $end_date, $description, $created_by);
    
    if (mysqli_stmt_execute($stmt)) {
        $report_id = mysqli_insert_id($conn);
        
        // Keep status as 'pending' (default) - admin will change it manually by clicking
        // Clear any output before sending JSON
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Report created successfully',
            'report_id' => $report_id
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create report: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    exit;
}

// PUT - Update report status
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $report_id = isset($data['id']) ? (int)$data['id'] : 0;
    $status = isset($data['status']) ? $data['status'] : null;
    
    if ($report_id <= 0 || !$status || !in_array($status, ['pending', 'done', 'failed'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    $sql = "UPDATE reports SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $report_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update report']);
    }
    
    mysqli_stmt_close($stmt);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>

