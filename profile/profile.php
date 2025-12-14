<?php
session_start();
require_once __DIR__ . '/../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';

// Fetch user profile data from database
$userProfileData = [];
$profileSql = "SELECT id_user, nama, email, role FROM user WHERE id_user = ?";
if ($stmt = mysqli_prepare($conn, $profileSql)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $userProfileData = $row;
        $userName = $row['nama'] ?? $userName;
        $userEmail = $row['email'] ?? $userEmail;
    }
    mysqli_stmt_close($stmt);
}

// Fetch adoption applications
$adoptionHistory = [];
$adoptSql = "SELECT a.id, a.hewan_id, a.status, a.submitted_at,
                    a.full_name, a.phone, a.address_line1, a.postcode,
                    h.namaHewan AS pet_name, h.jenis AS pet_type
             FROM adoption_applications a
             LEFT JOIN hewan h ON h.id_hewan = a.hewan_id
             WHERE a.applicant_user_id = ?
             ORDER BY a.submitted_at DESC
             LIMIT 10";
if ($stmt = mysqli_prepare($conn, $adoptSql)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $adoptionHistory[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Fetch rehome submissions
$rehomeHistory = [];
$rehomeSql = "SELECT id, pet_name, pet_type, breed, status, submitted_at
              FROM rehome_submissions
              WHERE user_id = ?
              ORDER BY submitted_at DESC
              LIMIT 10";
if ($stmt = mysqli_prepare($conn, $rehomeSql)) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $rehomeHistory[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get latest phone and location from adoption or rehome
$phone = '';
$location = '';
$livingInfo = '';

if (!empty($adoptionHistory)) {
    $latest = $adoptionHistory[0];
    $phone = $latest['phone'] ?? '';
    $addr = trim($latest['address_line1'] ?? '');
    $postcode = trim($latest['postcode'] ?? '');
    $location = $addr . (strlen($addr) && strlen($postcode) ? ', ' : '') . $postcode;
}

// Sanitization
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if (empty($date)) return '—';
    return date('d M Y', strtotime($date));
}

function formatStatus($status) {
    $status = strtolower($status ?? '');
    $statusMap = [
        'submitted' => 'Pending',
        'in_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn'
    ];
    return $statusMap[$status] ?? ucfirst($status);
}

function getStatusClass($status) {
    $status = strtolower($status ?? '');
    switch($status) {
        case 'approved': return 'status-approved';
        case 'rejected': return 'status-rejected';
        case 'in_review': return 'status-review';
        default: return 'status-pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PetResQ</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kreon:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/feather-icons"></script>
    
    <link rel="stylesheet" href="../css/style.css?v=2">
    <link rel="stylesheet" href="profile.css?v=1">
    <style>
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            padding-top: 60px;
            background-color: var(--bg);
        }
        main {
            background-color: var(--bg);
            min-height: calc(100vh - 200px);
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
        
        <div class="navbar-extra" aria-label="Top right taskbar">
            <a href="#" id="notification" aria-label="Notifications"><i data-feather="bell"></i></a>
            <button aria-label="User account" class="user-profile" id="user-profile" type="button">
                <div class="user-profile-inner">
                    <svg class="profile-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g data-name="Layer 2">
                            <path d="m15.626 11.769a6 6 0 1 0 -7.252 0 9.008 9.008 0 0 0 -5.374 8.231 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 9.008 9.008 0 0 0 -5.374-8.231zm-7.626-4.769a4 4 0 1 1 4 4 4 4 0 0 1 -4-4zm10 14h-12a1 1 0 0 1 -1-1 7 7 0 0 1 14 0 1 1 0 0 1 -1 1z"></path>
                        </g>
                    </svg>
                    <span class="user-name">LOGIN</span>
                    <span class="user-initial" id="user-initial"></span>
                </div>
            </button>
            <div class="profile-dropdown" id="profile-dropdown">
                <div class="dropdown-header">
                    <div class="dropdown-user-name" id="dropdown-name"></div>
                    <div class="dropdown-user-email" id="dropdown-email"></div>
                </div>
                <hr class="dropdown-divider">
                <a href="profile.php" class="dropdown-item">Profile</a>
                <hr class="dropdown-divider">
                <a href="#" id="logout-btn" class="dropdown-item">Logout</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="profile-layout">
            <!-- Sidebar Navigation -->
            <aside class="profile-sidebar">
            <div class="sidebar-item active" data-section="profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </div>
            <div class="sidebar-item" data-section="adoptions">
                <i class="fas fa-paw"></i>
                <span>Adopt</span>
            </div>
            <div class="sidebar-item" data-section="rehome">
                <i class="fas fa-home"></i>
                <span>Rehome</span>
            </div>
            <a class="sidebar-item" href="../logout.php">
                <i class="fas fa-right-from-bracket"></i>
                <span>Log Out</span>
            </a>
        </aside>

        <!-- Main Content -->
        <section class="profile-content">
            <!-- Profile Section -->
            <div class="content-section active" id="profile-section">
                <!-- Profile Header Card -->
                <div class="profile-header card">
                    <div class="avatar">
                        <div class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="user-meta">
                        <h2><?php echo e($userName); ?></h2>
                        <div class="meta-line"><i class="fas fa-envelope"></i> <?php echo e($userEmail); ?></div>
                    </div>
                    <div class="header-actions">
                        <button class="btn-outline" id="edit-profile-btn">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Adoptions Section -->
            <div class="content-section" id="adoptions-section">
                <div class="section-header">
                    <h2><i class="fas fa-heart"></i> My Adoption Submissions</h2>
                    <p class="section-subtitle">Showing all your adoption applications and their status</p>
                </div>

                <?php if (empty($adoptionHistory)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>You haven't submitted any adoption applications yet.</p>
                        <a href="../adopt/adopt.php" class="btn-primary">
                            <i class="fas fa-paw"></i> Browse Available Pets
                        </a>
                    </div>
                <?php else: ?>
                    <div class="submissions-grid">
                        <?php foreach ($adoptionHistory as $app): ?>
                            <div class="submission-card">
                                <div class="submission-header">
                                    <div>
                                        <div class="submission-title">
                                            <i class="fas fa-heart" style="color: #d63031; margin-right: 8px;"></i>
                                            <?php echo e($app['pet_name'] ?? 'Pet #' . (int)($app['hewan_id'] ?? 0)); ?>
                                        </div>
                                        <div class="submission-meta">
                                            <strong><?php echo e(($app['full_name'] ?? $userName)); ?></strong> • 
                                            <?php echo formatDate($app['submitted_at']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo getStatusClass($app['status']); ?>">
                                        <?php echo formatStatus($app['status']); ?>
                                    </span>
                                </div>

                                <div class="submission-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Email</div>
                                        <div class="detail-value"><?php echo e(($app['email'] ?? $userEmail)); ?></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Phone</div>
                                        <div class="detail-value <?php echo empty($app['phone']) ? 'empty' : ''; ?>">
                                            <?php echo e(($app['phone'] ?: 'Not provided')); ?>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Address</div>
                                        <div class="detail-value"><?php echo e(($app['address_line1'] ?? 'Not provided')); ?></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Postcode</div>
                                        <div class="detail-value"><?php echo e(($app['postcode'] ?? 'Not provided')); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rehome Section -->
            <div class="content-section" id="rehome-section">
                <div class="section-header">
                    <h2><i class="fas fa-home"></i> My Rehome Submissions</h2>
                    <p class="section-subtitle">Showing all your rehome applications and their status</p>
                </div>

                <?php if (empty($rehomeHistory)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>You haven't submitted any rehome applications yet.</p>
                        <a href="../rehome/rehome.html" class="btn-primary">
                            <i class="fas fa-home"></i> Submit Rehome
                        </a>
                    </div>
                <?php else: ?>
                    <div class="submissions-grid">
                        <?php foreach ($rehomeHistory as $item): ?>
                            <div class="submission-card">
                                <div class="submission-header">
                                    <div>
                                        <div class="submission-title">
                                            <i class="fas fa-home" style="color: #2d3436; margin-right: 8px;"></i>
                                            <?php echo e($item['pet_name']); ?> (<?php echo e($item['pet_type']); ?>)
                                        </div>
                                        <div class="submission-meta">
                                            Breed: <?php echo e($item['breed']); ?> • 
                                            <?php echo formatDate($item['submitted_at']); ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo getStatusClass($item['status']); ?>">
                                        <?php echo formatStatus($item['status']); ?>
                                    </span>
                                </div>

                                <div class="submission-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Pet Type</div>
                                        <div class="detail-value"><?php echo e($item['pet_type']); ?></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Breed</div>
                                        <div class="detail-value"><?php echo e($item['breed']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        </div>
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
    <script>
        feather.replace();

        // Handle sidebar navigation
        document.querySelectorAll('.sidebar-item[data-section]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');
                
                // Remove active class from all sidebar items
                document.querySelectorAll('.sidebar-item').forEach(si => {
                    si.classList.remove('active');
                });
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Show selected section
                const targetSection = document.getElementById(sectionId + '-section');
                if (targetSection) {
                    targetSection.classList.add('active');
                }
            });
        });
    </script>

    <!-- Edit Profile Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Profile</h3>
                <button class="modal-close" id="modal-close">&times;</button>
            </div>
            <form id="edit-form" class="modal-form">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" value="<?php echo e($userName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($userEmail); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="modal-cancel">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
            <div id="modal-message" class="modal-message" style="display: none;"></div>
        </div>
    </div>

    <script>
        // Edit Profile Modal Handler
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing modal...');
            
            const editBtn = document.getElementById('edit-profile-btn');
            const editModal = document.getElementById('edit-modal');
            const modalClose = document.getElementById('modal-close');
            const modalCancel = document.getElementById('modal-cancel');
            const editForm = document.getElementById('edit-form');
            const modalMessage = document.getElementById('modal-message');

            console.log('Edit button:', editBtn);
            console.log('Edit modal:', editModal);

            if (!editBtn || !editModal) {
                console.error('Modal elements not found');
                console.log('Available IDs:', {
                    editBtn: editBtn ? 'found' : 'NOT FOUND',
                    editModal: editModal ? 'found' : 'NOT FOUND'
                });
                return;
            }

            editBtn.addEventListener('click', function(e) {
                console.log('Edit button clicked');
                e.preventDefault();
                editModal.classList.add('open');
                console.log('Added open class to modal');
                document.body.style.overflow = 'hidden';
            });

            function closeModal() {
                editModal.classList.remove('open');
                document.body.style.overflow = 'auto';
                modalMessage.style.display = 'none';
            }

            modalClose.addEventListener('click', closeModal);
            modalCancel.addEventListener('click', closeModal);

            editModal.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    closeModal();
                }
            });

            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const nama = document.getElementById('nama').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (!nama) {
                    showMessage('Nama tidak boleh kosong', 'error');
                    return;
                }
                
                if (!email || !email.includes('@')) {
                    showMessage('Email tidak valid', 'error');
                    return;
                }

                try {
                    const response = await fetch('update_profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            nama: nama,
                            email: email
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                } catch (error) {
                    showMessage('Terjadi kesalahan: ' + error.message, 'error');
                }
            });

            function showMessage(message, type) {
                modalMessage.textContent = message;
                modalMessage.className = 'modal-message ' + type;
                modalMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>
