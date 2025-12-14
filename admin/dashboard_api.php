<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once __DIR__ . '/../config.php';

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

// Get total users
$usersQuery = "SELECT COUNT(*) as total FROM user";
$usersResult = mysqli_query($conn, $usersQuery);
$usersRow = mysqli_fetch_assoc($usersResult);
$totalUsers = (int)($usersRow['total'] ?? 0);

// Get total animals
$animalsQuery = "SELECT COUNT(*) as total FROM hewan WHERE status = 'Available'";
$animalsResult = mysqli_query($conn, $animalsQuery);
$animalsRow = mysqli_fetch_assoc($animalsResult);
$totalAnimals = (int)($animalsRow['total'] ?? 0);

// Get pending reports
$reportsQuery = "SELECT COUNT(*) as total FROM reports WHERE status = 'Pending' OR status = 'pending'";
$reportsResult = mysqli_query($conn, $reportsQuery);
$reportsRow = mysqli_fetch_assoc($reportsResult);
$pendingReports = (int)($reportsRow['total'] ?? 0);

// Get recent activity from adoption and rehome submissions
$recentActivity = [];

// Get recent adoption submissions
$adoptionQuery = "SELECT 
    a.id,
    a.submitted_at,
    a.status,
    u.nama as user_name,
    h.namaHewan as animal_name,
    'adoption' as type
FROM adoption_applications a
LEFT JOIN user u ON a.applicant_user_id = u.id_user
LEFT JOIN hewan h ON a.hewan_id = h.id_hewan
WHERE a.submitted_at IS NOT NULL
ORDER BY a.submitted_at DESC
LIMIT 10";

$adoptionResult = mysqli_query($conn, $adoptionQuery);
if ($adoptionResult) {
    while ($row = mysqli_fetch_assoc($adoptionResult)) {
        $animalName = $row['animal_name'] ?? 'N/A';
        $userName = $row['user_name'] ?? 'Unknown';
        $recentActivity[] = [
            'id' => (int)$row['id'],
            'type' => 'adoption',
            'user_name' => $userName,
            'animal_name' => $animalName,
            'status' => $row['status'] ?? 'pending',
            'date' => $row['submitted_at'],
            'description' => "Adoption application for {$animalName} by {$userName}"
        ];
    }
}

// Get recent rehome submissions
$rehomeQuery = "SELECT 
    rs.id,
    rs.submitted_at,
    rs.status,
    rs.pet_name,
    u.nama as user_name,
    'rehome' as type
FROM rehome_submissions rs
LEFT JOIN user u ON rs.user_id = u.id_user
WHERE rs.submitted_at IS NOT NULL
ORDER BY rs.submitted_at DESC
LIMIT 10";

$rehomeResult = mysqli_query($conn, $rehomeQuery);
if ($rehomeResult) {
    while ($row = mysqli_fetch_assoc($rehomeResult)) {
        $petName = $row['pet_name'] ?? 'N/A';
        $userName = $row['user_name'] ?? 'Unknown';
        $recentActivity[] = [
            'id' => (int)$row['id'],
            'type' => 'rehome',
            'user_name' => $userName,
            'animal_name' => $petName,
            'status' => $row['status'] ?? 'submitted',
            'date' => $row['submitted_at'],
            'description' => "Rehome submission for {$petName} by {$userName}"
        ];
    }
}

// Sort by date (most recent first)
usort($recentActivity, function($a, $b) {
    $timeA = strtotime($a['date'] ?? '1970-01-01');
    $timeB = strtotime($b['date'] ?? '1970-01-01');
    return $timeB - $timeA;
});

// Limit to 10 most recent
$recentActivity = array_slice($recentActivity, 0, 10);

// Get chart data (new users, new animals, system reports in last 30 days)
$thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

// New users in last 30 days - check if created_at column exists
$newUsers = 0;
$checkUserCol = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'created_at'");
if (mysqli_num_rows($checkUserCol) > 0) {
    $newUsersQuery = "SELECT COUNT(*) as total FROM user WHERE created_at >= ?";
    $newUsersStmt = mysqli_prepare($conn, $newUsersQuery);
    if ($newUsersStmt) {
        mysqli_stmt_bind_param($newUsersStmt, 's', $thirtyDaysAgo);
        mysqli_stmt_execute($newUsersStmt);
        $newUsersResult = mysqli_stmt_get_result($newUsersStmt);
        $newUsersRow = mysqli_fetch_assoc($newUsersResult);
        $newUsers = (int)($newUsersRow['total'] ?? 0);
        mysqli_stmt_close($newUsersStmt);
    }
} else {
    // If no created_at column, use total users as approximation (all users are "new")
    $newUsers = $totalUsers;
}

// New animals in last 30 days - check if created_at column exists, or use animals from rehome submissions
$newAnimals = 0;
$checkHewanCol = mysqli_query($conn, "SHOW COLUMNS FROM hewan LIKE 'created_at'");
if (mysqli_num_rows($checkHewanCol) > 0) {
    $newAnimalsQuery = "SELECT COUNT(*) as total FROM hewan WHERE created_at >= ? AND status = 'Available'";
    $newAnimalsStmt = mysqli_prepare($conn, $newAnimalsQuery);
    if ($newAnimalsStmt) {
        mysqli_stmt_bind_param($newAnimalsStmt, 's', $thirtyDaysAgo);
        mysqli_stmt_execute($newAnimalsStmt);
        $newAnimalsResult = mysqli_stmt_get_result($newAnimalsStmt);
        $newAnimalsRow = mysqli_fetch_assoc($newAnimalsResult);
        $newAnimals = (int)($newAnimalsRow['total'] ?? 0);
        mysqli_stmt_close($newAnimalsStmt);
    }
} else {
    // Count animals that came from rehome submissions in last 30 days
    $newAnimalsQuery = "SELECT COUNT(DISTINCT h.id_hewan) as total 
                        FROM hewan h
                        INNER JOIN rehome_submissions rs ON h.main_photo = rs.pet_image_path
                        WHERE rs.submitted_at >= ? AND h.status = 'Available'";
    $newAnimalsStmt = mysqli_prepare($conn, $newAnimalsQuery);
    if ($newAnimalsStmt) {
        mysqli_stmt_bind_param($newAnimalsStmt, 's', $thirtyDaysAgo);
        mysqli_stmt_execute($newAnimalsStmt);
        $newAnimalsResult = mysqli_stmt_get_result($newAnimalsStmt);
        $newAnimalsRow = mysqli_fetch_assoc($newAnimalsResult);
        $newAnimals = (int)($newAnimalsRow['total'] ?? 0);
        mysqli_stmt_close($newAnimalsStmt);
    }
    // If still 0, use total animals as approximation
    if ($newAnimals == 0) {
        $newAnimals = $totalAnimals;
    }
}

// System reports in last 30 days
$newReports = 0;
$checkReportsCol = mysqli_query($conn, "SHOW COLUMNS FROM reports LIKE 'created_at'");
if (mysqli_num_rows($checkReportsCol) > 0) {
    $newReportsQuery = "SELECT COUNT(*) as total FROM reports WHERE created_at >= ?";
    $newReportsStmt = mysqli_prepare($conn, $newReportsQuery);
    if ($newReportsStmt) {
        mysqli_stmt_bind_param($newReportsStmt, 's', $thirtyDaysAgo);
        mysqli_stmt_execute($newReportsStmt);
        $newReportsResult = mysqli_stmt_get_result($newReportsStmt);
        $newReportsRow = mysqli_fetch_assoc($newReportsResult);
        $newReports = (int)($newReportsRow['total'] ?? 0);
        mysqli_stmt_close($newReportsStmt);
    }
} else {
    // If no created_at column, use total reports
    $allReportsQuery = "SELECT COUNT(*) as total FROM reports";
    $allReportsResult = mysqli_query($conn, $allReportsQuery);
    $allReportsRow = mysqli_fetch_assoc($allReportsResult);
    $newReports = (int)($allReportsRow['total'] ?? 0);
}

$response = [
    'total_users' => $totalUsers,
    'total_animals' => $totalAnimals,
    'pending_reports' => $pendingReports,
    'recent_activity' => $recentActivity,
    'chart_data' => [
        'new_users' => $newUsers,
        'new_animals' => $newAnimals,
        'system_reports' => $newReports
    ]
];

echo json_encode($response);
?>

