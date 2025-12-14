<?php
session_start();
require_once '../config.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Get animal ID from URL parameter
$animalId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animalId == 0) {
    die("Animal ID not provided");
}

// Fetch animal data from database
$query = "SELECT * FROM hewan WHERE id_hewan = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $animalId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$animal = mysqli_fetch_assoc($result);

if (!$animal) {
    die("Animal not found");
}

// Debug: log the fetched data
error_log("Fetched animal: " . json_encode($animal));

// Build photo path - adjust for relative path from adopt folder
$mainPhoto = '';
if (!empty($animal['main_photo'])) {
    // If path starts with uploads/, add ../ to go up one level from adopt folder
    $photoPath = $animal['main_photo'];
    if (strpos($photoPath, 'uploads/') === 0) {
        $mainPhoto = '../' . $photoPath;
    } else {
        $mainPhoto = $photoPath;
    }
    $mainPhoto = htmlspecialchars($mainPhoto);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($animal['namaHewan']); ?> - PetResQ</title>

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
    <link rel="stylesheet" href="styleprofile.css?v=2" />
  </head>
  <body>
    <!-- Navbar start -->
    <nav class="navbar">
      <a href="../index.html" class="navbar-logo">Pet<span>ResQ</span></a>

      <div class="navbar-nav">
        <a href="../index.html">Home</a>
        <a href="adopt.php">Adopt</a>
        <a href="../rehome/rehome.html">Rehome</a>
        <a href="#care-guides">Care Guides</a>
        
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

    <!-- Login/Register Modal -->
    <div class="modal" id="auth-modal">
      <div class="modal-content modal-horizontal">
        <!-- Foto Hewan Login -->
        <div class="modal-image modal-image-login active">
          <img src="../icon/login2.jpg" alt="Login" />
        </div>

        <!-- Foto Hewan Register -->
        <div class="modal-image modal-image-register">
          <img src="../icon/register.jpg" alt="Register" />
        </div>

        <!-- Form Container -->
        <div class="modal-form-container">
          <div class="modal-header">
            <h2 id="modal-title">Login</h2>
            <button class="close-btn" id="close-modal">&times;</button>
          </div>

          <!-- Error Message Display -->
          <div class="form-error" id="form-error" style="display: none"></div>

          <!-- Login Form -->
          <form class="tab-content active" id="login-tab" method="POST">
            <div class="form-group">
              <label for="login-email">Email</label>
              <input
                type="email"
                id="login-email"
                name="email"
                placeholder="Enter your email"
                required
              />
            </div>

            <div class="form-group">
              <label for="login-password">Password</label>
              <input
                type="password"
                id="login-password"
                name="password"
                placeholder="Enter your password"
                required
              />
            </div>

            <div class="form-group checkbox">
              <input type="checkbox" id="remember-me" />
              <label for="remember-me">Remember me</label>
            </div>

            <button type="submit" class="btn-submit">Login</button>

            <p class="form-footer">
              Don't have an account?
              <a href="#" class="switch-tab" data-tab="register"
                >Register here</a
              >
            </p>
          </form>

          <!-- Register Form -->
          <form class="tab-content" id="register-tab" method="POST">
            <div class="form-group">
              <label for="register-name">Full Name</label>
              <input
                type="text"
                id="register-name"
                name="nama"
                placeholder="Enter your full name"
                required
              />
            </div>

            <div class="form-group">
              <label for="register-email">Email</label>
              <input
                type="email"
                id="register-email"
                name="email"
                placeholder="Enter your email"
                required
              />
            </div>

            <div class="form-group">
              <label for="register-password">Password</label>
              <input
                type="password"
                id="register-password"
                name="password"
                placeholder="Enter your password"
                required
              />
            </div>

            <div class="form-group">
              <label for="register-confirm">Confirm Password</label>
              <input
                type="password"
                id="register-confirm"
                name="confirm_password"
                placeholder="Confirm your password"
                required
              />
            </div>

            <div class="form-group checkbox">
              <input type="checkbox" id="agree-terms" required />
              <label for="agree-terms">I agree to the Terms & Conditions</label>
            </div>

            <button type="submit" class="btn-submit">Register</button>

            <p class="form-footer">
              Already have an account?
              <a href="#" class="switch-tab" data-tab="login">Login here</a>
            </p>
          </form>
        </div>
      </div>
    </div>

    <!-- Main Animal Profile Page -->
    <main class="animal-profile-page">
      <div class="profile-container">
        <!-- Greeting -->
        <div class="greeting">Hi Human!</div>

        <!-- Animal Header with Name and ID -->
        <div class="animal-header">
          <div class="animal-avatar"></div>
          <div class="animal-basic-info">
            <div class="animal-name" id="profile-name"><?php echo htmlspecialchars($animal['namaHewan']); ?></div>
            <div class="animal-id" id="profile-id">Pet ID: <?php echo htmlspecialchars($animal['id_hewan']); ?></div>
            <div class="animal-type" id="profile-type"><?php echo htmlspecialchars($animal['jenis']); ?></div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="profile-content">
          <!-- Left Side - Photo and Details -->
          <div class="profile-left">
            <!-- Animal Photo -->
            <div class="animal-photo-section" id="profile-photo">
              <?php if (!empty($mainPhoto)): ?>
                <img src="<?php echo $mainPhoto; ?>" alt="<?php echo htmlspecialchars($animal['namaHewan']); ?>" onerror="this.parentElement.innerHTML='<div style=&quot;display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background-color: #e0e0e0; font-size: 18px; color: #666;&quot;>No Image Available</div>';" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background-color: #e0e0e0; font-size: 18px; color: #666;">No Image Available</div>
              <?php endif; ?>
            </div>

            <!-- Animal Details Grid -->
            <div class="details-grid">
              <div class="detail-item">
                <i data-feather="users"></i>
                <div>
                  <div class="detail-label-title">Gender</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['gender']); ?></span>
                </div>
              </div>
              <div class="detail-item">
                <i data-feather="heart"></i>
                <div>
                  <div class="detail-label-title">Breed</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['breed']); ?></span>
                </div>
              </div>
              <div class="detail-item">
                <i data-feather="clock"></i>
                <div>
                  <div class="detail-label-title">Age</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['age']); ?></span>
                </div>
              </div>
              <div class="detail-item">
                <i data-feather="droplet"></i>
                <div>
                  <div class="detail-label-title">Color</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['color']); ?></span>
                </div>
              </div>
              <div class="detail-item">
                <i data-feather="square"></i>
                <div>
                  <div class="detail-label-title">Weight</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['weight']); ?> kg</span>
                </div>
              </div>
              <div class="detail-item">
                <i data-feather="chevrons-up"></i>
                <div>
                  <div class="detail-label-title">Height</div>
                  <span class="detail-label"><?php echo htmlspecialchars($animal['height']); ?> cm</span>
                </div>
              </div>
            </div>

            <!-- Vaccination Info -->
            <div class="vaccination-section">
              <div class="vaccination-header">Vaccinated</div>
              <div class="vaccination-table">
                <div class="vac-row">
                  <div class="vac-col">Vaksin 1</div>
                  <div class="vac-col">Vaksin 2</div>
                  <div class="vac-col">Vaksin 3</div>
                  <div class="vac-col">Vaksin 4</div>
                </div>
                <div class="vac-row">
                  <div class="vac-col">Vaksin 5</div>
                  <div class="vac-col">Vaksin 6</div>
                  <div class="vac-col">Vaksin 7</div>
                  <div class="vac-col">Vaksin 8</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Side - Story and Information -->
          <div class="profile-right">
            <!-- Animal Story -->
            <div class="story-section">
              <div class="story-header">Animal Story</div>
              <div class="story-content" id="profile-story">
                <p><?php echo nl2br(htmlspecialchars($animal['description'])); ?></p>
              </div>
            </div>

            <!-- Contact Owner -->
            <button class="btn-contact">Start chat</button>

            <!-- Adoption CTA -->
            <div class="adoption-cta">
              <?php if ($animal['status'] === 'Adopted'): ?>
                <div class="adoption-question" style="color: #d9534f; font-weight: bold;">
                  This animal has been adopted
                </div>
                <button class="btn-get-started" id="adoptStartBtn" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.6;">Already Adopted</button>
              <?php else: ?>
                <div class="adoption-question">
                  If you are interested to adopt
                </div>
                <button class="btn-get-started" id="adoptStartBtn">Get started</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>
    <!-- Main Animal Profile Page end -->

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

    <!-- Feather Icons -->
    <script>
      feather.replace();
      
      // Log animal data to console
      const animalData = <?php echo json_encode($animal); ?>;
      console.log('Animal Data:', animalData);
      console.log('Animal Name:', animalData.namaHewan);
      console.log('Animal Breed:', animalData.breed);
      console.log('Animal Gender:', animalData.gender);
      console.log('Animal Age:', animalData.age);
      console.log('Animal Color:', animalData.color);
      console.log('Animal Weight:', animalData.weight);
      console.log('Animal Height:', animalData.height);
    </script>

    <!-- My Javascript -->
    <script src="../js/script.js"></script>

    <script>
      // Wait for DOM to be ready and script.js to be loaded
      document.addEventListener('DOMContentLoaded', function() {
        // Setup modal listeners from script.js
        if (typeof setupModalListeners === 'function') {
          setupModalListeners();
        }

        // Handle Get Started button with login check
        const adoptStartBtn = document.getElementById('adoptStartBtn');
        if (adoptStartBtn && !adoptStartBtn.disabled) {
          adoptStartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if animal is already adopted (from PHP status)
            const animalStatus = '<?php echo htmlspecialchars($animal['status'] ?? 'Available'); ?>';
            if (animalStatus === 'Adopted') {
              alert('This animal has already been adopted.');
              return;
            }
            
            // Check if user is logged in
            fetch('../check_session.php')
              .then(response => response.json())
              .then(data => {
                console.log('Login check on button click:', data);
                if (data.is_logged_in) {
                  // User is logged in, proceed to adoption form
                  console.log('User logged in, redirecting to sistem adopt...');
                  try {
                    const petId = (window.animalData && window.animalData.id_hewan) ? window.animalData.id_hewan : (new URLSearchParams(window.location.search).get('id') || '');
                    const url = new URL('../sistemadopt/index.html', window.location.href);
                    if (petId) url.searchParams.set('id_hewan', petId);
                    window.location.href = url.toString();
                  } catch (e) {
                    // Fallback if URL API not available
                    const petId = (window.animalData && window.animalData.id_hewan) ? window.animalData.id_hewan : '';
                    window.location.href = '../sistemadopt/index.html' + (petId ? ('?id_hewan=' + encodeURIComponent(petId)) : '');
                  }
                } else {
                  // User not logged in, show login modal
                  console.log('User not logged in, showing modal...');
                  const authModal = document.getElementById('auth-modal');
                  if (authModal) {
                    authModal.classList.add('active');
                  }
                }
              })
              .catch(error => {
                console.error('Error checking login:', error);
                // Fallback: show login modal
                const authModal = document.getElementById('auth-modal');
                if (authModal) {
                  authModal.classList.add('active');
                }
              });
          });
        }
      });
    </script>
  </body>
</html>
