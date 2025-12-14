<?php
session_start();
require_once __DIR__ . '/../config.php';

// Simple role check: only allow admin users
// Assuming session stores user role in $_SESSION['role'] ('admin'|'user')
if (!isset($_SESSION['user_id'])) {
  header('Location: /PetResQ/login.php');
  exit;
}
$role = $_SESSION['role'] ?? 'user';
if ($role !== 'admin') {
  http_response_code(403);
  echo 'Forbidden: Admins only';
  exit;
}

// Determine which tab is active
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'adoption';

// Fetch recent adoption applications
$apps = [];
$sql = "SELECT a.id, a.applicant_user_id, u.nama AS applicant_name, u.email AS applicant_email,
               a.full_name, a.email, a.phone, a.address_line1, a.city, a.postcode,
               a.has_garden, a.living_situation, a.status, a.submitted_at
        FROM adoption_applications a
        LEFT JOIN `user` u ON u.id_user = a.applicant_user_id
        ORDER BY a.submitted_at DESC LIMIT 100";

$res = mysqli_query($conn, $sql);
if ($res) {
  while ($row = mysqli_fetch_assoc($res)) {
    $apps[] = $row;
  }
}

// Fetch rehome submissions
$rehomes = [];
$rehome_sql = "SELECT rs.id, rs.user_id, rs.pet_name, rs.pet_type, rs.age_years, rs.breed, rs.color, 
                       rs.weight, rs.height, rs.gender, rs.city, rs.postcode, rs.spayed_neutered, 
                       rs.rehome_reason, rs.status, rs.submitted_at, u.nama, u.email
                FROM rehome_submissions rs
                LEFT JOIN user u ON u.id_user = rs.user_id
                ORDER BY rs.submitted_at DESC LIMIT 100";

$rehome_res = mysqli_query($conn, $rehome_sql);
if ($rehome_res) {
  while ($row = mysqli_fetch_assoc($rehome_res)) {
    $rehomes[] = $row;
  }
}

// Fetch unread notifications for admin
$notes = [];
$noteSql = "SELECT id, application_id, message, is_read, created_at
            FROM notifications
            WHERE recipient_user_id = ?
            ORDER BY created_at DESC LIMIT 50";
$noteStmt = mysqli_prepare($conn, $noteSql);
$adminId = (int)$_SESSION['user_id'];
if ($noteStmt) {
  mysqli_stmt_bind_param($noteStmt, 'i', $adminId);
  mysqli_stmt_execute($noteStmt);
  $noteRes = mysqli_stmt_get_result($noteStmt);
  if ($noteRes) {
    while ($row = mysqli_fetch_assoc($noteRes)) {
      $notes[] = $row;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Submissions</title>
  <link rel="stylesheet" href="/PetResQ/css/style.css?v=2" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; padding: 20px; background-color: #fefae0; }
    .container { max-width: 1400px; margin: 0 auto; }
    h1 { margin-bottom: 10px; color: #2c3e50; }
    .section { margin-top: 24px; }
    
    /* Tabs */
    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 2px solid #ddd;
    }
    
    .tab-btn {
      padding: 12px 24px;
      background: transparent;
      border: none;
      border-bottom: 3px solid transparent;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      color: #666;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
    }
    
    .tab-btn:hover { color: #5f6f52; }
    .tab-btn.active { color: #5f6f52; border-bottom-color: #5f6f52; }
    
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    th, td { padding: 12px; text-align: left; }
    th { background: #f9f9f9; font-weight: 600; color: #333; border-bottom: 2px solid #ddd; }
    td { border-bottom: 1px solid #eee; }
    tr:hover { background-color: #f9f9f9; }
    
    .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-submitted { background: #fff3cd; color: #856404; }
    .status-in_review { background: #cfe2ff; color: #084298; }
    .status-approved { background: #d1e7dd; color: #0f5132; }
    .status-rejected { background: #f8d7da; color: #842029; }
    .status-withdrawn { background: #e2e3e5; color: #383d41; }
    
    .actions { display: flex; gap: 8px; }
    .btn { padding: 6px 12px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 4px; font-size: 12px; font-weight: 600; transition: all 0.3s; font-family: 'Poppins', sans-serif; }
    .btn:hover { background-color: #f0f0f0; }
    .btn-view { background-color: #5f6f52; color: white; border: none; }
    .btn-view:hover { background-color: #4a5640; }
  </style>
</head>
<body>
  <div class="container">
    <h1>📋 Submissions Management</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>.</p>

    <div class="section">
      <h2>Notifications</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Application</th>
            <th>Message</th>
            <th>Read</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($notes)) : ?>
            <tr><td colspan="5" style="text-align:center;">No notifications</td></tr>
          <?php else: foreach ($notes as $n): ?>
            <tr>
              <td><?php echo (int)$n['id']; ?></td>
              <td><?php echo (int)$n['application_id']; ?></td>
              <td><?php echo htmlspecialchars($n['message']); ?></td>
              <td><?php echo ((int)$n['is_read'] ? 'Yes' : 'No'); ?></td>
              <td><?php echo htmlspecialchars($n['created_at']); ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h2>Submissions</h2>
      
      <!-- Tabs -->
      <div class="tabs">
        <button class="tab-btn <?php echo $tab === 'adoption' ? 'active' : ''; ?>" onclick="switchTab('adoption')">
          🐾 Adoption Applications
        </button>
        <button class="tab-btn <?php echo $tab === 'rehome' ? 'active' : ''; ?>" onclick="switchTab('rehome')">
          🏠 Rehome Submissions
        </button>
      </div>

      <!-- Adoption Tab -->
      <div id="adoption-tab" class="tab-content <?php echo $tab === 'adoption' ? 'active' : ''; ?>">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Applicant</th>
              <th>Contact</th>
              <th>Address</th>
              <th>Garden</th>
              <th>Living</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($apps)) : ?>
              <tr><td colspan="9" style="text-align:center;">No applications found</td></tr>
            <?php else: foreach ($apps as $a): ?>
              <tr>
                <td><?php echo (int)$a['id']; ?></td>
                <td>
                  <?php echo htmlspecialchars($a['applicant_name'] ?? $a['full_name'] ?? ''); ?><br>
                  <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($a['applicant_email'] ?? $a['email'] ?? ''); ?></span>
                </td>
                <td><?php echo htmlspecialchars($a['phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($a['address_line1']); ?>, <?php echo htmlspecialchars($a['city'] ?? ''); ?> (<?php echo htmlspecialchars($a['postcode']); ?>)</td>
                <td><?php echo ((int)$a['has_garden'] ? 'Yes' : 'No'); ?></td>
                <td><?php echo htmlspecialchars($a['living_situation'] ?? ''); ?></td>
                <td><span class="badge status-<?php echo htmlspecialchars($a['status']); ?>"><?php echo htmlspecialchars($a['status']); ?></span></td>
                <td><?php echo htmlspecialchars($a['submitted_at']); ?></td>
                <td>
                  <div class="actions">
                    <button class="btn btn-view">View</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Rehome Tab -->
      <div id="rehome-tab" class="tab-content <?php echo $tab === 'rehome' ? 'active' : ''; ?>">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Pet Info</th>
              <th>Type & Gender</th>
              <th>Owner Details</th>
              <th>Location</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rehomes)) : ?>
              <tr><td colspan="8" style="text-align:center;">No rehome submissions found</td></tr>
            <?php else: foreach ($rehomes as $r): ?>
              <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td>
                  <strong><?php echo htmlspecialchars($r['pet_name']); ?></strong><br>
                  <span style="font-size: 12px; color: #666;">Age: <?php echo (int)$r['age_years']; ?> years | <?php echo htmlspecialchars($r['breed']); ?></span>
                </td>
                <td>
                  <span class="badge" style="background: #f0f0f0; color: #333;"><?php echo htmlspecialchars($r['pet_type']); ?></span><br>
                  <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($r['gender']); ?></span>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($r['nama'] ?? 'Unknown'); ?></strong><br>
                  <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($r['email'] ?? 'N/A'); ?></span>
                </td>
                <td>
                  <?php echo htmlspecialchars($r['city']); ?><br>
                  <span style="font-size: 12px; color: #666;">Postcode: <?php echo htmlspecialchars($r['postcode']); ?></span>
                </td>
                <td><span class="badge status-<?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
                <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
                <td>
                  <div class="actions">
                    <a href="rehome_detail.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-view">View</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function switchTab(tabName) {
      // Hide all tabs
      document.getElementById('adoption-tab').classList.remove('active');
      document.getElementById('rehome-tab').classList.remove('active');
      
      // Remove active from all buttons
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      
      // Show selected tab and button
      document.getElementById(tabName + '-tab').classList.add('active');
      event.target.classList.add('active');
      
      // Update URL without reloading
      window.history.pushState({}, '', '?tab=' + tabName);
    }
  </script>
</body>
</html>
