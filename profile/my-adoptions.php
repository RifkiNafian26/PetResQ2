<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';

// Fetch user's adoption applications with full details
$adoptionApps = [];
$adoptSql = "SELECT a.id, a.hewan_id, a.status, a.submitted_at, a.updated_at,
                    a.full_name, a.email, a.phone, a.address_line1, a.postcode,
                    a.has_garden, a.living_situation, a.story,
                    h.namaHewan AS pet_name, h.jenis AS pet_type
             FROM adoption_applications a
             LEFT JOIN hewan h ON h.id_hewan = a.hewan_id
             WHERE a.applicant_user_id = ?
             ORDER BY a.submitted_at DESC";
if ($stmt = mysqli_prepare($conn, $adoptSql)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $adoptionApps[] = $row;
    }
    mysqli_stmt_close($stmt);
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if (empty($date)) return '—';
    return date('d M Y H:i', strtotime($date));
}

function getStatusBadgeClass($status) {
    $status = strtolower($status ?? '');
    switch($status) {
        case 'approved': return 'badge-success';
        case 'rejected': return 'badge-danger';
        case 'in_review': return 'badge-warning';
        default: return 'badge-secondary';
    }
}

function formatStatusText($status) {
    $status = strtolower($status ?? '');
    $map = [
        'submitted' => 'Pending',
        'in_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn'
    ];
    return $map[$status] ?? ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Adoption Submissions - PetResQ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kreon:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/feather-icons"></script>
    
    <link rel="stylesheet" href="../css/style.css?v=2">
    <style>
        body {
            padding-top: 60px;
            background-color: var(--bg, #fefae0);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: #5f6f52;
            font-size: 2rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .page-header .subtitle {
            color: #888;
            font-size: 0.95rem;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: #5f6f52;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            gap: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 20px;
            margin: 40px 0;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #888;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .empty-state a {
            display: inline-block;
            padding: 10px 24px;
            background: #5f6f52;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .empty-state a:hover {
            background: #4a5a3f;
        }
        
        .submission-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .submission-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            border-color: #5f6f52;
        }
        
        .submission-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            margin-bottom: 16px;
            align-items: start;
        }
        
        .submission-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2a2a2a;
            margin-bottom: 8px;
        }
        
        .submission-meta {
            color: #888;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .submission-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #efefef;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 0.95rem;
            color: #333;
            line-height: 1.4;
        }
        
        .detail-value.empty {
            color: #bbb;
            font-style: italic;
        }
        
        footer {
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            main {
                margin: 30px auto;
            }
            
            .page-header h1 {
                font-size: 1.6rem;
            }
            
            .submission-header {
                grid-template-columns: 1fr;
            }
            
            .submission-details {
                grid-template-columns: 1fr;
            }
            
            .submission-card {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="../index.php" class="navbar-logo">Pet<span>ResQ</span></a>
        
        <div class="navbar-nav">
            <a href="../index.php">Home</a>
            <a href="../adopt/adopt.php">Adopt</a>
            <a href="../rehome/rehome.html">Rehome</a>
            <div class="navbar-dropdown">
                <a href="#care-guides" class="care-guides-link">Care Guides</a>
                <div class="dropdown-menu">
                    <a href="../careguides/Dog Care Guides.html">Dog</a>
                    <a href="../careguides/Cat Care Guides.html">Cat</a>
                    <a href="../careguides/Rabbit Care Guides.html">Rabbit</a>
                </div>
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../Admin PetResQ/index.php">Admin</a>
            <?php endif; ?>
        </div>
        
        <div class="navbar-extra">
            <a href="#" id="notification" aria-label="Notifications"><i data-feather="bell"></i></a>
            <button class="user-profile" id="user-profile" type="button">
                <div class="user-profile-inner">
                    <svg class="profile-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g data-name="Layer 2">
                            <path d="m15.626 11.769a6 6 0 1 0 -7.252 0 9.008 9.008 0 0 0 -5.374 8.231 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 9.008 9.008 0 0 0 -5.374-8.231zm-7.626-4.769a4 4 0 1 1 4 4 4 4 0 0 1 -4-4zm10 14h-12a1 1 0 0 1 -1-1 7 7 0 0 1 14 0 1 1 0 0 1 -1 1z"></path>
                        </g>
                    </svg>
                    <span class="user-name">LOGIN</span>
                    <span class="user-initial"></span>
                </div>
            </button>
            <div class="profile-dropdown" id="profile-dropdown">
                <div class="dropdown-header">
                    <div class="dropdown-user-name"></div>
                    <div class="dropdown-user-email"></div>
                </div>
                <hr class="dropdown-divider">
                <a href="#" id="logout-btn" class="dropdown-item">Logout</a>
            </div>
        </div>
    </nav>

    <main>
        <!-- Back Button -->
        <a href="profile.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Profile
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-heart"></i> My Adoption Submissions</h1>
            <div class="subtitle">Showing all your adoption applications and their status</div>
        </div>

        <!-- Content -->
        <?php if (empty($adoptionApps)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>You haven't submitted any adoption applications yet.</p>
                <a href="../adopt/adopt.php">
                    <i class="fas fa-paw"></i> Browse Available Pets
                </a>
            </div>
        <?php else: ?>
            <!-- Submission Cards -->
            <div class="submissions-list">
                <?php foreach ($adoptionApps as $app): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div>
                                <div class="submission-title">
                                    <i class="fas fa-heart" style="color: #d63031; margin-right: 8px;"></i>
                                    <?php echo e($app['pet_name'] ?? ('Pet #' . (int)($app['hewan_id'] ?? 0))); ?>
                                    <?php if (!empty($app['pet_type'])): ?>
                                        <span style="font-weight: normal; color: #888; font-size: 0.9rem;">
                                            (<?php echo e($app['pet_type']); ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="submission-meta">
                                    <strong><?php echo e($app['full_name']); ?></strong> • 
                                    <?php echo formatDate($app['submitted_at']); ?>
                                </div>
                            </div>
                            <span class="badge <?php echo getStatusBadgeClass($app['status']); ?>">
                                <?php echo formatStatusText($app['status']); ?>
                            </span>
                        </div>

                        <div class="submission-details">
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value"><?php echo e($app['email']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value <?php echo empty($app['phone']) ? 'empty' : ''; ?>">
                                    <?php echo e($app['phone'] ?: 'Not provided'); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Address</div>
                                <div class="detail-value"><?php echo e($app['address_line1']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Postcode</div>
                                <div class="detail-value"><?php echo e($app['postcode']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Has Garden</div>
                                <div class="detail-value">
                                    <?php echo (int)$app['has_garden'] ? 'Yes' : 'No'; ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Living Situation</div>
                                <div class="detail-value <?php echo empty($app['living_situation']) ? 'empty' : ''; ?>">
                                    <?php echo e($app['living_situation'] ?: 'Not provided'); ?>
                                </div>
                            </div>
                            <?php if (!empty($app['story'])): ?>
                                <div class="detail-item" style="grid-column: 1 / -1;">
                                    <div class="detail-label">Your Story</div>
                                    <div class="detail-value" style="background: #f9f9f9; padding: 12px; border-radius: 8px; max-height: 120px; overflow-y: auto;">
                                        <?php echo e($app['story']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">PetResQ</div>
            </div>
            <div class="footer-right">
                <h3>Contact Us</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Jl. Pendidikan No.15, Cibiru Wetan, Kec. Cileunyi, Kabupaten Bandung.</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <span>+62 898-6099-362</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>kampus_cibiru@upi.edu</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/script.js?v=3"></script>
    <script>feather.replace();</script>
</body>
</html>
