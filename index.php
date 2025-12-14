<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PetResQ</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link 
      href="https://fonts.googleapis.com/css2?family=Kavoon&family=Kreon:wght@300;400;500;600;700&display=swap" 
      rel="stylesheet">
    <link 
      rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://unpkg.com/feather-icons"></script>

    <link rel="stylesheet" href="css/style.css?v=2" />
  </head>
  <body>
    <nav class="navbar">
      <a href="index.php" class="navbar-logo">Pet<span>ResQ</span></a>

      <div class="navbar-nav">
        <a href="index.php">Home</a>
        <a href="adopt/adopt.php">Adopt</a>
        <a href="rehome/rehome.html">Rehome</a>
        <div class="navbar-dropdown">
          <a href="#care-guides" class="care-guides-link">Care Guides</a>
          <div class="dropdown-menu">
            <a href="careguides/Dog Care Guides.html">Dog</a>
            <a href="careguides/Cat Care Guides.html">Cat</a>
            <a href="careguides/Rabbit Care Guides.html">Rabbit</a>
          </div>
        </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="Admin PetResQ/index.php">Admin</a>
        <?php endif; ?>
      </div>

      <div class="navbar-extra" aria-label="Top right taskbar">
        <a href="#" id="notification" aria-label="Notifications"><i data-feather="bell"></i></a>

        <button aria-label="User account" class="user-profile" id="user-profile" type="button">
          <div class="user-profile-inner">
            <svg class="profile-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g data-name="Layer 2" id="Layer_2">
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
          <hr class="dropdown-divider" />
          <a href="profile/profile.php" class="dropdown-item">Profile</a>
          <hr class="dropdown-divider" />
          <a href="#" id="logout-btn" class="dropdown-item">Logout</a>
        </div>
      </div>
    </nav>

    <?php include __DIR__ . '/index.html.content.php'; ?>

    <!-- Login/Register Modal -->
    <div class="modal" id="auth-modal">
      <div class="modal-content modal-horizontal">
        <!-- Foto Hewan Login -->
        <div class="modal-image modal-image-login active">
          <img src="icon/login2.jpg" alt="Login" />
        </div>

        <!-- Foto Hewan Register -->
        <div class="modal-image modal-image-register">
          <img src="icon/register.jpg" alt="Register" />
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
    
    <script>
      feather.replace();
    </script>
    
    <script src="js/script.js?v=3"></script>
  </body>
  </html>
