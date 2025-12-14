<?php
session_start();
require_once __DIR__ . '/../config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validate input
if (empty($nama)) {
    echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
    exit;
}

// Check if email already exists (excluding current user)
$emailCheckSql = "SELECT id_user FROM user WHERE email = ? AND id_user != ?";
if ($stmt = mysqli_prepare($conn, $emailCheckSql)) {
    mysqli_stmt_bind_param($stmt, 'si', $email, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh user lain']);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Update profile
$updateSql = "UPDATE user SET nama = ?, email = ? WHERE id_user = ?";
if ($stmt = mysqli_prepare($conn, $updateSql)) {
    mysqli_stmt_bind_param($stmt, 'ssi', $nama, $email, $userId);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session
        $_SESSION['user_name'] = $nama;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profil berhasil diperbarui',
            'nama' => $nama,
            'email' => $email
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
