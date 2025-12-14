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

// Fetch all rehome submissions with user info
$query = "SELECT rs.id, rs.user_id, rs.pet_name, rs.pet_type, rs.age_years, rs.breed, 
                 rs.gender, rs.city, rs.postcode, rs.spayed_neutered, rs.rehome_reason,
                 rs.status, rs.submitted_at, u.nama, u.email
          FROM rehome_submissions rs
          LEFT JOIN user u ON rs.user_id = u.id_user
          ORDER BY rs.submitted_at DESC";

$result = mysqli_query($conn, $query);
$submissions = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $submissions[] = $row;
    }
}

// Get stats
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_review' THEN 1 ELSE 0 END) as in_review,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM rehome_submissions";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result) ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rehome Submissions - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fefae0;
            padding: 30px;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #5f6f52 0%, #4a5640 100%);
            padding: 30px 20px;
            color: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            font-size: 20px;
            font-weight: 700;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .header-greeting {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-badge {
            background-color: #5f6f52;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            margin-left: 10px;
        }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            flex-wrap: wrap;
        }
        
        .filter-bar select, .filter-bar input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 0 10px;
        }
        
        .submission-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #5f6f52;
        }
        
        .submission-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-id {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        .badge-withdrawn {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .card-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #333;
        }
        
        .card-item i {
            width: 20px;
            text-align: center;
            color: #5f6f52;
            font-size: 16px;
        }
        
        .card-item.name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .card-item.email {
            color: #666;
            font-size: 13px;
        }
        
        .card-item.date {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-card {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-view {
            background-color: #5f6f52;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #4a5640;
        }
        
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #138496;
        }
        
        .stat-pills {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .pill {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background-color: white;
            border: 2px solid #ddd;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .pill:hover, .pill.active {
            background-color: #5f6f52;
            color: white;
            border-color: #5f6f52;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .pet-type-badge {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-paw"></i>
            <span>PetResQ</span>
        </div>
        <nav class="sidebar-nav">
            <a href="#"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#"><i class="fas fa-users"></i> Manage Users</a>
            <a href="#"><i class="fas fa-paw"></i> Manage Animals</a>
            <a href="#"><i class="fas fa-file-alt"></i> System Reports</a>
            <a href="#" class="active"><i class="fas fa-inbox"></i> Submissions</a>
            <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header-top">
            <div class="header-greeting">Welcome MinQ!!</div>
            <div class="header-user">
                <div class="admin-badge">Admin</div>
            </div>
        </div>
        
        <!-- Section Title -->
        <h2 class="section-title">Rehome Submissions</h2>
        
        <!-- Status Filters -->
        <div class="stat-pills">
            <button class="pill active" onclick="filterByStatus('')">
                <i class="fas fa-list"></i> All
            </button>
            <button class="pill" onclick="filterByStatus('submitted')">
                <i class="fas fa-hourglass-start"></i> Pending <span style="background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; margin-left: 5px;"><?php echo $stats['pending'] ?? 0; ?></span>
            </button>
            <button class="pill" onclick="filterByStatus('in_review')">
                <i class="fas fa-eye"></i> In Review <span style="background: #cfe2ff; color: #084298; padding: 2px 6px; border-radius: 10px; margin-left: 5px;"><?php echo $stats['in_review'] ?? 0; ?></span>
            </button>
            <button class="pill" onclick="filterByStatus('approved')">
                <i class="fas fa-check-circle"></i> Approved <span style="background: #d1e7dd; color: #0f5132; padding: 2px 6px; border-radius: 10px; margin-left: 5px;"><?php echo $stats['approved'] ?? 0; ?></span>
            </button>
            <button class="pill" onclick="filterByStatus('rejected')">
                <i class="fas fa-times-circle"></i> Rejected <span style="background: #f8d7da; color: #842029; padding: 2px 6px; border-radius: 10px; margin-left: 5px;"><?php echo $stats['rejected'] ?? 0; ?></span>
            </button>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <select id="filter-type" onchange="filterCards()">
                <option value="">All Pet Types</option>
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Rabbit">Rabbit</option>
            </select>
            <input type="text" id="search-pet" placeholder="Search pet name..." onkeyup="filterCards()">
        </div>
        
        <!-- Submissions Cards Grid -->
        <?php if (empty($submissions)): ?>
        <div class="no-data">
            <i class="fas fa-inbox"></i>
            <p>No rehome submissions yet.</p>
        </div>
        <?php else: ?>
        <div class="cards-grid" id="cards-container">
            <?php foreach ($submissions as $sub): ?>
            <div class="submission-card" 
                 data-status="<?php echo $sub['status']; ?>" 
                 data-type="<?php echo $sub['pet_type']; ?>"
                 data-pet="<?php echo strtolower($sub['pet_name']); ?>">
                
                <div class="card-header">
                    <div class="card-id">#<?php echo str_pad($sub['id'], 3, '0', STR_PAD_LEFT); ?></div>
                    <span class="status-badge badge-<?php echo $sub['status']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $sub['status'])); ?>
                    </span>
                </div>
                
                <div class="card-item name">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($sub['nama'] ?? 'Unknown'); ?>
                </div>
                
                <div class="card-item">
                    <i class="fas fa-paw"></i>
                    <span>
                        <strong><?php echo htmlspecialchars($sub['pet_name']); ?></strong><br>
                        <span class="pet-type-badge"><?php echo $sub['pet_type']; ?></span>
                        <span style="font-size: 12px; color: #999;">Age: <?php echo $sub['age_years']; ?> yrs</span>
                    </span>
                </div>
                
                <div class="card-item email">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($sub['email'] ?? 'N/A'); ?>
                </div>
                
                <div class="card-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($sub['city']); ?>
                </div>
                
                <div class="card-item date">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($sub['submitted_at'])); ?>
                </div>
                
                <div class="card-actions">
                    <a href="rehome_detail.php?id=<?php echo $sub['id']; ?>" class="btn-card btn-view">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="rehome_detail.php?id=<?php echo $sub['id']; ?>&edit=1" class="btn-card btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function filterByStatus(status) {
            // Update active pill
            document.querySelectorAll('.pill').forEach(pill => pill.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter cards
            filterCards(status);
        }
        
        function filterCards(statusFilter = '') {
            const typeFilter = document.getElementById('filter-type')?.value.toLowerCase() || '';
            const searchFilter = document.getElementById('search-pet')?.value.toLowerCase() || '';
            
            document.querySelectorAll('.submission-card').forEach(card => {
                const status = card.dataset.status.toLowerCase();
                const type = card.dataset.type.toLowerCase();
                const pet = card.dataset.pet.toLowerCase();
                
                const statusMatch = !statusFilter || status === statusFilter;
                const typeMatch = !typeFilter || type === typeFilter;
                const petMatch = !searchFilter || pet.includes(searchFilter);
                
                card.style.display = (statusMatch && typeMatch && petMatch) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
