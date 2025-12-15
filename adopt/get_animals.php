<?php
require_once '../config.php';

// Get filter parameters
$animalType = isset($_GET['animal']) ? $_GET['animal'] : [];
$breed = isset($_GET['breed']) ? $_GET['breed'] : [];
$color = isset($_GET['color']) ? $_GET['color'] : [];
$age = isset($_GET['age']) ? $_GET['age'] : [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query - include both Available and Adopted animals
// Adopted animals will be shown but disabled
$query = "SELECT * FROM hewan WHERE status IN ('Available', 'Adopted')";
$conditions = [];
$params = [];
$types = '';

// Add search condition: match by name, breed, or jenis (animal type)
if (!empty($search)) {
    $conditions[] = "(namaHewan LIKE ? OR breed LIKE ? OR jenis LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

// Add animal type filter using correct column 'jenis'
if (!empty($animalType) && is_array($animalType)) {
    $placeholders = str_repeat('?,', count($animalType) - 1) . '?';
    $conditions[] = "jenis IN ($placeholders)";
    foreach ($animalType as $type) {
        $params[] = $type;
        $types .= 's';
    }
}

// Add breed filter
if (!empty($breed) && is_array($breed)) {
    $placeholders = str_repeat('?,', count($breed) - 1) . '?';
    $conditions[] = "breed IN ($placeholders)";
    foreach ($breed as $b) {
        $params[] = $b;
        $types .= 's';
    }
}

// Add color filter
if (!empty($color) && is_array($color)) {
    $placeholders = str_repeat('?,', count($color) - 1) . '?';
    $conditions[] = "color IN ($placeholders)";
    foreach ($color as $c) {
        $params[] = $c;
        $types .= 's';
    }
}

// Add age filter
if (!empty($age) && is_array($age)) {
    $placeholders = str_repeat('?,', count($age) - 1) . '?';
    $conditions[] = "age IN ($placeholders)";
    foreach ($age as $a) {
        $params[] = $a;
        $types .= 's';
    }
}

// Combine conditions
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY id_hewan DESC";

// Execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Fetch animals
$animals = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Use main_photo path directly from database
    if (empty($row['main_photo'])) {
        $row['main_photo'] = '';
    }
    // Ensure status is included and properly formatted
    if (!isset($row['status']) || empty($row['status'])) {
        $row['status'] = 'Available';
    }
    $animals[] = $row;
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($animals);
?>
