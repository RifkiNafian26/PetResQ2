<?php
session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Adopt - PetResQ</title>

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
    <link rel="stylesheet" href="styleadopt.css?v=3" />
  </head>
  <body>
    <!-- Navbar start -->
    <nav class="navbar">
      <a href="../index.php" class="navbar-logo">Pet<span>ResQ</span></a>

      <div class="navbar-nav">
        <a href="../index.php">Home</a>
        <a href="#">Adopt</a>
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

    <!-- Main Adopt Page -->
    <main class="adopt-page">
      <!-- Search/Hero Section -->
      <section class="search-section">
        <h2>Find an Animal to Adopt</h2>
        <form class="search-box" id="search-form">
          <input type="text" id="search-input" placeholder="Search by name or breed..." />
          <button type="submit"><i data-feather="search"></i></button>
        </form>
      </section>

      <!-- Main Content Container with Sidebar -->
      <div class="adopt-container">
        <!-- Filter Section (Sidebar) -->
        <section class="filter-section">
          <div class="filter-header">
            <h3 class="filter-title">Filters</h3>
            <button class="reset-filters">Reset Filters</button>
          </div>

          <div class="filter-controls">
            <!-- Animal Type Filter -->
            <div class="filter-group">
              <div class="filter-label-header">
                <label class="filter-label">Animal Type</label>
                <button class="filter-toggle" aria-expanded="true">
                  <i data-feather="chevron-down"></i>
                </button>
              </div>
              <div class="filter-options">
                <div class="filter-option">
                  <input type="checkbox" id="dog" name="animal" value="Dog" />
                  <label for="dog">Dog</label>
                </div>
                <div class="filter-option">
                  <input type="checkbox" id="cat" name="animal" value="Cat" />
                  <label for="cat">Cat</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="rabbit"
                    name="animal"
                    value="Rabbit"
                  />
                  <label for="rabbit">Rabbit</label>
                </div>
              </div>
            </div>

            <!-- Breed Filter -->
            <div class="filter-group">
              <div class="filter-label-header">
                <label class="filter-label">Breed</label>
                <button class="filter-toggle" aria-expanded="true">
                  <i data-feather="chevron-down"></i>
                </button>
              </div>
              <div class="filter-options">
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="golden-retriever"
                    name="breed"
                    value="Golden Retriever"
                  />
                  <label for="golden-retriever">Golden Retriever</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="beagle"
                    name="breed"
                    value="Beagle"
                  />
                  <label for="beagle">Beagle</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="poodle"
                    name="breed"
                    value="Poodle"
                  />
                  <label for="poodle">Poodle</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="husky"
                    name="breed"
                    value="Siberian Husky"
                  />
                  <label for="husky">Siberian Husky</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Shiba Inu"
                    name="breed"
                    value="Shiba Inu"
                  />
                  <label for="Shiba Inu">Shiba Inu</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Bengal"
                    name="breed"
                    value="Bengal"
                  />
                  <label for="Bengal">Bengal</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="British Shorthair"
                    name="breed"
                    value="British Shorthair"
                  />
                  <label for="British Shorthair">British Shorthair</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Himalayan"
                    name="breed"
                    value="Himalayan"
                  />
                  <label for="Himalayan">Himalayan</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Alaskan"
                    name="breed"
                    value="Alaskan"
                  />
                  <label for="Alaskan">Alaskan</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Havana"
                    name="breed"
                    value="Havana"
                  />
                  <label for="Havana">Havana</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Flemish Giant"
                    name="breed"
                    value="Flemish Giant"
                  />
                  <label for="Flemish Giant">Flemish Giant</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Holland Hop"
                    name="breed"
                    value="Holland Hop"
                  />
                  <label for="Holland Hop">Holland Hop</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="Lionhead"
                    name="breed"
                    value="Lionhead"
                  />
                  <label for="Lionhead">Lionhead</label>
                </div>
                <div class="filter-option">
                  <input type="checkbox" id="Rex" name="breed" value="Rex" />
                  <label for="Rex">Rex</label>
                </div>
              </div>
            </div>

            <!-- Color Filter -->
            <div class="filter-group">
              <div class="filter-label-header">
                <label class="filter-label">Color</label>
                <button class="filter-toggle" aria-expanded="true">
                  <i data-feather="chevron-down"></i>
                </button>
              </div>
              <div class="filter-options">
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="golden"
                    name="color"
                    value="Golden"
                  />
                  <label for="golden">Golden</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="brown"
                    name="color"
                    value="Brown"
                  />
                  <label for="brown">Brown</label>
                </div>
                <div class="filter-option">
                  <input type="checkbox" id="Gray" name="color" value="Gray" />
                  <label for="Gray">Gray</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="black"
                    name="color"
                    value="Black"
                  />
                  <label for="black">Black</label>
                </div>
              </div>
            </div>

            <!-- Age Filter -->
            <div class="filter-group">
              <div class="filter-label-header">
                <label class="filter-label">Age</label>
                <button class="filter-toggle" aria-expanded="true">
                  <i data-feather="chevron-down"></i>
                </button>
              </div>
              <div class="filter-options">
                <div class="filter-option">
                  <input type="checkbox" id="young" name="age" value="Young" />
                  <label for="young">Young</label>
                </div>
                <div class="filter-option">
                  <input type="checkbox" id="adult" name="age" value="Adult" />
                  <label for="adult">Adult</label>
                </div>
                <div class="filter-option">
                  <input
                    type="checkbox"
                    id="senior"
                    name="age"
                    value="Senior"
                  />
                  <label for="senior">Senior</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Apply Filter Button (hidden - filters are real-time) -->
          <button class="apply-filter-btn" style="display: none">
            Apply Your Filter
          </button>
        </section>

        <!-- Animal Cards Grid -->
        <section class="animals-section">
          <div class="animal-grid" id="animal-grid">
            <!-- Animals will be loaded here by JavaScript -->
          </div>
        </section>

        <!-- Pagination -->
        <section class="pagination-section">
          <nav class="pagination">
            <button class="pagination-btn prev">Prev</button>
            <button class="pagination-btn active">1</button>
            <button class="pagination-btn">2</button>
            <button class="pagination-btn">3</button>
            <button class="pagination-btn">4</button>
            <button class="pagination-btn">5</button>
            <button class="pagination-btn next">Next</button>
          </nav>
        </section>
      </div>
    </main>
    <!-- Main Adopt Page end -->

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
    </script>

    <!-- My Javascript -->
    <script src="../js/script.js?v=3"></script>
    <script src="adopt-filter.js?v=3"></script>
  </body>
</html>
