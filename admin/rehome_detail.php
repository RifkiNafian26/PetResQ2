<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Check if admin
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get submission ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    die("Submission ID not provided");
}

// Fetch submission
$query = "SELECT rs.*, u.nama, u.email 
          FROM rehome_submissions rs
          LEFT JOIN user u ON rs.user_id = u.id_user
          WHERE rs.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$submission = mysqli_fetch_assoc($result);

if (!$submission) {
    die("Submission not found");
}

// Decode documents JSON
$documents = !empty($submission['documents_json']) ? json_decode($submission['documents_json'], true) : [];

// Check if update message
$updated = isset($_GET['updated']) ? true : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission #<?php echo $id; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 12px;
            color: #5f6f52;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #5f6f52;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background-color: #5f6f52;
            color: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #5f6f52;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
        }
        
        .header-meta {
            text-align: right;
            font-size: 14px;
            color: #666;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
        
        .badge-submitted {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-in_review {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .badge-approved {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .badge-rejected {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-grid.full {
            grid-template-columns: 1fr;
        }
        
        .info-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid #5f6f52;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 15px;
            color: #333;
            line-height: 1.5;
        }
        
        .pet-image {
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .story-text {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            line-height: 1.6;
            color: #555;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .document-list {
            list-style: none;
            padding: 0;
        }
        
        .document-item {
            background-color: #f9f9f9;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid #5f6f52;
        }
        
        .document-name {
            flex: 1;
            font-weight: 500;
            color: #333;
        }
        
        .document-link {
            display: inline-block;
            padding: 6px 12px;
            background-color: #5f6f52;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .document-link:hover {
            background-color: #4a5640;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-primary {
            background-color: #5f6f52;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4a5640;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .no-documents {
            color: #999;
            font-style: italic;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="rehome_submissions.php" class="back-link">← Back to Submissions</a>
        
        <div class="header">
            <h1>Rehome Submission #<?php echo $submission['id']; ?></h1>
            <div class="header-meta">
                <p>Submitted: <strong><?php echo date('M d, Y H:i', strtotime($submission['submitted_at'])); ?></strong></p>
                <p>Last Updated: <strong><?php echo date('M d, Y H:i', strtotime($submission['updated_at'])); ?></strong></p>
            </div>
        </div>
        
        <?php if ($updated): ?>
        <div class="alert-success">
            ✓ Status updated successfully!
        </div>
        <?php endif; ?>
        
        <div>
            <span class="status-badge badge-<?php echo $submission['status']; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $submission['status'])); ?>
            </span>
        </div>

        <!-- Submitter Information -->
        <div class="section">
            <div class="section-title">Submitter Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['nama'] ?? 'Unknown'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><a href="mailto:<?php echo htmlspecialchars($submission['email'] ?? ''); ?>"><?php echo htmlspecialchars($submission['email'] ?? 'N/A'); ?></a></div>
                </div>
            </div>
        </div>

        <!-- Pet Information -->
        <div class="section">
            <div class="section-title">Pet Information</div>
            
            <?php if (!empty($submission['pet_image_path'])): ?>
            <div style="margin-bottom: 20px;">
                <img src="../<?php echo htmlspecialchars($submission['pet_image_path']); ?>" alt="Pet Image" class="pet-image" onerror="this.src='../icon/no-image.png'">
            </div>
            <?php endif; ?>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Pet Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['pet_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Type</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['pet_type']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['gender']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Age</div>
                    <div class="info-value"><?php echo $submission['age_years']; ?> years</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Breed</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['breed']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Color</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['color']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Weight</div>
                    <div class="info-value"><?php echo $submission['weight']; ?> kg</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Height</div>
                    <div class="info-value"><?php echo $submission['height']; ?> cm</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Spayed/Neutered</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['spayed_neutered']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reason to Rehome</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['rehome_reason']); ?></div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="section">
            <div class="section-title">Location Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['address_line1']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['city']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Postcode</div>
                    <div class="info-value"><?php echo htmlspecialchars($submission['postcode']); ?></div>
                </div>
            </div>
        </div>

        <!-- Pet Story -->
        <div class="section">
            <div class="section-title">Pet Story</div>
            <div class="story-text"><?php echo htmlspecialchars($submission['pet_story']); ?></div>
        </div>

        <!-- Documents -->
        <div class="section">
            <div class="section-title">Documents</div>
            <?php if (!empty($documents)): ?>
            <ul class="document-list">
                <?php foreach ($documents as $idx => $doc): ?>
                <li class="document-item">
                    <span class="document-name">
                        📄 <?php echo htmlspecialchars(basename($doc)); ?>
                    </span>
                    <a href="../<?php echo htmlspecialchars($doc); ?>" target="_blank" class="document-link">Download</a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="no-documents">No documents uploaded</p>
            <?php endif; ?>
        </div>

        <!-- Admin Actions -->
        <div class="section">
            <div class="section-title">Admin Actions</div>
            <div class="action-buttons">
                <a href="rehome_update.php?id=<?php echo $submission['id']; ?>&status=in_review" class="btn btn-primary">Mark as In Review</a>
                <a href="rehome_update.php?id=<?php echo $submission['id']; ?>&status=approved" class="btn btn-success">✓ Approve</a>
                <a href="rehome_update.php?id=<?php echo $submission['id']; ?>&status=rejected" class="btn btn-danger">✗ Reject</a>
            </div>
        </div>
    </div>
</body>
</html>
