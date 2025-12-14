<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /PetResQ/login.php');
  exit;
}
$userId = (int)$_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'User';
$email = $_SESSION['user_email'] ?? '';

// Fetch user's adoption applications
$apps = [];
$sql = "SELECT a.id, a.hewan_id, a.status, a.submitted_at,
               a.address_line1, a.postcode, a.living_situation,
               h.namaHewan AS pet_name, h.jenis AS pet_type
        FROM adoption_applications a
        LEFT JOIN hewan h ON h.id_hewan = a.hewan_id
        WHERE a.applicant_user_id = ?
        ORDER BY a.submitted_at DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
  mysqli_stmt_bind_param($stmt, 'i', $userId);
  if (mysqli_stmt_execute($stmt)) {
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
      while ($row = mysqli_fetch_assoc($res)) {
        $apps[] = $row;
      }
    }
  } else {
    // Log error for debugging
    error_log("Failed to execute adoption applications query for user $userId: " . mysqli_stmt_error($stmt));
  }
  mysqli_stmt_close($stmt);
} else {
  // Log error for debugging
  error_log("Failed to prepare adoption applications query for user $userId: " . mysqli_error($conn));
}

// Fetch user's rehome submissions
$rehomes = [];
$rehome_sql = "SELECT id, pet_name, pet_type, age_years, breed, gender, 
                      status, submitted_at, updated_at
               FROM rehome_submissions
               WHERE user_id = ?
               ORDER BY submitted_at DESC";
$rehome_stmt = mysqli_prepare($conn, $rehome_sql);
if ($rehome_stmt) {
  mysqli_stmt_bind_param($rehome_stmt, 'i', $userId);
  mysqli_stmt_execute($rehome_stmt);
  $rehome_res = mysqli_stmt_get_result($rehome_stmt);
  if ($rehome_res) {
    while ($row = mysqli_fetch_assoc($rehome_res)) {
      $rehomes[] = $row;
    }
  }
  mysqli_stmt_close($rehome_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Submissions - PetResQ</title>
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&family=Poppins:wght@100;300;400;700&display=swap"
    rel="stylesheet"
  />
  <!-- Feather Icon -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- My Style -->
  <link rel="stylesheet" href="../css/style.css?v=2" />
  <style>
    body { 
      padding-top: 60px; 
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main { 
      flex: 1;
      max-width: 1000px; 
      margin: 24px auto; 
      padding: 0 16px; 
      width: 100%; 
    }
    footer {
      margin-top: auto;
    }
    h1 { margin-bottom: 8px; }
    h2 { margin-bottom: 16px; margin-top: 16px; }
    .subtitle { color: #555; margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #f7f7f7; font-weight: 600; }
    td { font-size: 13px; }
    .badge { display:inline-block; padding:3px 8px; border-radius:12px; font-size:12px; font-weight:500; }
    .status-submitted { background:#eef; color:#225; }
    .status-in_review { background:#ffe; color:#552; }
    .status-approved { background:#efe; color:#252; }
    .status-rejected { background:#fee; color:#522; }
  </style>
</head>
<body>
  <!-- Navbar start -->
  <nav class="navbar">
    <a href="../index.html" class="navbar-logo">Pet<span>ResQ</span></a>

    <div class="navbar-nav">
      <a href="../index.html">Home</a>
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
    </div>

    <div class="navbar-extra" aria-label="Top right taskbar">
      <a href="#" id="notification" aria-label="Notifications"
        ><i data-feather="bell"></i
      ></a>

      <button
        aria-label="User account"
        class="user-profile"
        id="user-profile"
        type="button"
      >
        <div class="user-profile-inner">
          <svg
            class="profile-icon"
            aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
          >
            <g data-name="Layer 2" id="Layer_2">
              <path
                d="m15.626 11.769a6 6 0 1 0 -7.252 0 9.008 9.008 0 0 0 -5.374 8.231 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 9.008 9.008 0 0 0 -5.374-8.231zm-7.626-4.769a4 4 0 1 1 4 4 4 4 0 0 1 -4-4zm10 14h-12a1 1 0 0 1 -1-1 7 7 0 0 1 14 0 1 1 0 0 1 -1 1z"
              ></path>
            </g>
          </svg>
          <span class="user-name">LOGIN</span>
          <span class="user-initial" id="user-initial"></span>
        </div>
      </button>

      <!-- Profile Dropdown Menu -->
      <div class="profile-dropdown" id="profile-dropdown">
        <div class="dropdown-header">
          <div class="dropdown-user-name" id="dropdown-name"></div>
          <div class="dropdown-user-email" id="dropdown-email"></div>
        </div>
        <hr class="dropdown-divider" />
        <a href="#" id="logout-btn" class="dropdown-item">Logout</a>
      </div>
    </div>
  </nav>
  <!-- Navbar end -->

  <main>
    <h1>My Submissions</h1>
    <div class="subtitle">Welcome, <?php echo htmlspecialchars($name); ?> (<?php echo htmlspecialchars($email); ?>)</div>

    <h2>Adoption Applications</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Pet</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Address</th>
          <th>Living</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($apps)) : ?>
          <tr><td colspan="6" style="text-align:center;">No submissions yet</td></tr>
        <?php else: 
          // Debug: uncomment to see data
          // error_log("Found " . count($apps) . " adoption applications for user $userId");
          foreach ($apps as $a): ?>
          <tr>
            <td><?php echo (int)$a['id']; ?></td>
            <td>
              <?php echo htmlspecialchars($a['pet_name'] ?? 'Unknown'); ?>
              <?php if (!empty($a['hewan_id'])): ?>
                <small>(ID: <?php echo (int)$a['hewan_id']; ?>)</small>
              <?php endif; ?>
            </td>
            <td><span class="badge status-<?php echo htmlspecialchars($a['status']); ?>"><?php echo htmlspecialchars($a['status']); ?></span></td>
            <td><?php echo htmlspecialchars($a['submitted_at']); ?></td>
            <td><?php echo htmlspecialchars($a['address_line1'] ?? ''); ?> (<?php echo htmlspecialchars($a['postcode'] ?? ''); ?>)</td>
            <td><?php echo htmlspecialchars($a['living_situation'] ?? ''); ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>

    <h2>Rehome Submissions</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Pet Name</th>
          <th>Type</th>
          <th>Breed</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Last Updated</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rehomes)) : ?>
          <tr><td colspan="7" style="text-align:center;">No rehome submissions yet</td></tr>
        <?php else: foreach ($rehomes as $r): ?>
          <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo htmlspecialchars($r['pet_name']); ?></td>
            <td><?php echo htmlspecialchars($r['pet_type']); ?></td>
            <td><?php echo htmlspecialchars($r['breed']); ?></td>
            <td><span class="badge status-<?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
            <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
            <td><?php echo htmlspecialchars($r['updated_at'] ?? $r['submitted_at']); ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </main>

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

  <script src="../js/script.js"></script>
  <script>
    feather.replace();
  </script>
</body>
</html>
