// Reset Filter Button
const resetBtn = document.querySelector(".reset-filters");

if (resetBtn) {
  resetBtn.addEventListener("click", function () {
    // Ambil semua checkbox di filter
    const checkboxes = document.querySelectorAll(
      '.filter-option input[type="checkbox"]'
    );

    // Uncheck semua checkbox
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    // Tampilkan semua animal cards
    const allCards = document.querySelectorAll(".animal-card");
    allCards.forEach((card) => {
      card.style.display = "block";
    });

    console.log("Semua filter telah direset!");
  });
}

// Filter Functionality
const applyFilterBtn = document.querySelector(".apply-filter-btn");

if (applyFilterBtn) {
  applyFilterBtn.addEventListener("click", function () {
    // Ambil semua checkbox yang dicek
    const checkedFilters = {
      animal: [],
      breed: [],
      color: [],
      age: [],
    };

    // Kumpulkan nilai checkbox yang dicek
    document
      .querySelectorAll('.filter-option input[type="checkbox"]:checked')
      .forEach((checkbox) => {
        const filterType = checkbox.name;
        const filterValue = checkbox.value;
        if (checkedFilters[filterType]) {
          checkedFilters[filterType].push(filterValue);
        }
      });

    // Ambil semua animal cards
    const allCards = document.querySelectorAll(".animal-card");

    // Filter animal cards
    allCards.forEach((card) => {
      let shouldShow = true;

      // Cek animal type
      if (
        checkedFilters.animal.length > 0 &&
        !checkedFilters.animal.includes(card.dataset.animal)
      ) {
        shouldShow = false;
      }

      // Cek breed
      if (
        shouldShow &&
        checkedFilters.breed.length > 0 &&
        !checkedFilters.breed.includes(card.dataset.breed)
      ) {
        shouldShow = false;
      }

      // Cek color
      if (
        shouldShow &&
        checkedFilters.color.length > 0 &&
        !checkedFilters.color.includes(card.dataset.color)
      ) {
        shouldShow = false;
      }

      // Cek age
      if (
        shouldShow &&
        checkedFilters.age.length > 0 &&
        !checkedFilters.age.includes(card.dataset.age)
      ) {
        shouldShow = false;
      }

      // Tampilkan atau sembunyikan card
      card.style.display = shouldShow ? "block" : "none";
    });

    console.log("Filter diterapkan:", checkedFilters);
  });
}

// Login/Register Modal
const modal = document.getElementById("auth-modal");
const userProfileBtn = document.getElementById("user-profile");
const closeModalBtn = document.getElementById("close-modal");
const switchTabLinks = document.querySelectorAll(".switch-tab");
const loginImage = document.querySelector(".modal-image-login");
const registerImage = document.querySelector(".modal-image-register");
const modalTitle = document.getElementById("modal-title");

// Event listener untuk profile button akan diatur oleh checkUserLogin()
// (tidak ditambahkan di sini agar tidak konflik dengan login/logout logic)

// Close modal when clicking close button
if (closeModalBtn) {
  closeModalBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });
}

// Close modal when clicking outside
if (modal) {
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
}

// Switch tab via link
switchTabLinks.forEach((link) => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    const tabName = this.getAttribute("data-tab");

    // Get all tab contents
    const tabContents = document.querySelectorAll(".tab-content");

    // Remove active class from all contents
    tabContents.forEach((content) => content.classList.remove("active"));

    // Add active class to target content
    document.getElementById(tabName + "-tab").classList.add("active");

    // Clear error message
    const errorDiv = document.getElementById("form-error");
    if (errorDiv) {
      errorDiv.style.display = "none";
      errorDiv.textContent = "";
    }

    // Switch images
    if (tabName === "login") {
      if (loginImage) loginImage.classList.add("active");
      if (registerImage) registerImage.classList.remove("active");
      // Update title
      if (modalTitle) modalTitle.textContent = "Login";
    } else if (tabName === "register") {
      if (loginImage) loginImage.classList.remove("active");
      if (registerImage) registerImage.classList.add("active");
      // Update title
      if (modalTitle) modalTitle.textContent = "Register";
    }
  });
});

// Prevent closing modal when clicking inside content
if (modal) {
  const modalContent = modal.querySelector(".modal-content");
  if (modalContent) {
    modalContent.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }
}

// Reinitialize Feather Icons after DOM changes
if (typeof feather !== "undefined") {
  feather.replace();
}

// Check user login status on page load
function checkUserLogin() {
  fetch("check_session.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.is_logged_in) {
        displayUserProfile(data.user_name, data.user_email);
      } else {
        displayLoginButton();
      }
    })
    .catch((error) => console.error("Error checking login:", error));
}

// Display user profile with initials
function displayUserProfile(userName, userEmail) {
  const userProfile = document.getElementById("user-profile");
  const userName_span = userProfile.querySelector(".user-name");
  const userInitial = userProfile.querySelector(".user-initial");
  const profileIcon = userProfile.querySelector(".profile-icon");

  // Hide icon and "LOGIN" text
  if (profileIcon) profileIcon.style.display = "none";
  if (userName_span) userName_span.style.display = "none";

  // Show user name
  if (userInitial) {
    userInitial.textContent = userName;
    userInitial.style.display = "inline-block";
  }

  // Update dropdown menu with user info
  const dropdownName = document.getElementById("dropdown-name");
  const dropdownEmail = document.getElementById("dropdown-email");
  if (dropdownName) dropdownName.textContent = userName;
  if (dropdownEmail) dropdownEmail.textContent = userEmail;

  // Close modal if open
  const modal = document.getElementById("auth-modal");
  if (modal) {
    modal.classList.remove("active");
  }

  // Replace element to remove old event listeners
  const newUserProfile = userProfile.cloneNode(true);
  userProfile.parentNode.replaceChild(newUserProfile, userProfile);

  // Add event listener for dropdown toggle
  newUserProfile.addEventListener("click", toggleProfileMenu);
}

// Display login button
function displayLoginButton() {
  const userProfile = document.getElementById("user-profile");
  const userName_span = userProfile.querySelector(".user-name");
  const userInitial = userProfile.querySelector(".user-initial");
  const profileIcon = userProfile.querySelector(".profile-icon");

  // Show icon and "LOGIN" text
  if (profileIcon) profileIcon.style.display = "block";
  if (userName_span) userName_span.style.display = "inline";

  // Hide initials
  if (userInitial) {
    userInitial.style.display = "none";
  }

  // Close dropdown
  const dropdown = document.getElementById("profile-dropdown");
  if (dropdown) {
    dropdown.classList.remove("active");
  }

  // Remove all previous event listeners and add fresh one for login
  userProfile.replaceWith(userProfile.cloneNode(true));
  const newUserProfile = document.getElementById("user-profile");
  newUserProfile.addEventListener("click", openLoginModal);
}

// Open login modal
function openLoginModal(e) {
  e.stopPropagation();
  const modal = document.getElementById("auth-modal");
  if (modal) {
    modal.classList.add("active");
  }
}

// Toggle profile dropdown menu
function toggleProfileMenu(e) {
  e.stopPropagation();
  const dropdown = document.getElementById("profile-dropdown");
  if (dropdown) {
    dropdown.classList.toggle("active");
  }
}

// Close dropdown when clicking outside
document.addEventListener("click", function (e) {
  const dropdown = document.getElementById("profile-dropdown");
  const userProfile = document.getElementById("user-profile");

  if (
    dropdown &&
    userProfile &&
    !userProfile.contains(e.target) &&
    !dropdown.contains(e.target)
  ) {
    dropdown.classList.remove("active");
  }
});

// Handle form submissions with AJAX
function handleFormSubmit(formId, phpFile) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    const errorDiv = document.getElementById("form-error");

    fetch(phpFile, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Success - reload to check login status
          setTimeout(() => {
            location.reload();
          }, 500);
        } else {
          // Show error message
          if (errorDiv) {
            errorDiv.textContent = data.message;
            errorDiv.style.display = "block";
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        if (errorDiv) {
          errorDiv.textContent = "An error occurred. Please try again.";
          errorDiv.style.display = "block";
        }
      });
  });
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  // Check login status
  checkUserLogin();

  // Setup form submissions
  handleFormSubmit("login-tab", "login.php");
  handleFormSubmit("register-tab", "register.php");

  // Setup logout button
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "logout.php";
    });
  }

  // Close dropdown when clicking navbar links
  const navbarLinks = document.querySelectorAll(".navbar-nav a");
  navbarLinks.forEach((link) => {
    link.addEventListener("click", function () {
      const dropdown = document.getElementById("profile-dropdown");
      if (dropdown) {
        dropdown.classList.remove("active");
      }
    });
  });

  // Close modal and dropdown when navigating
  const notification = document.getElementById("notification");
  if (notification) {
    notification.addEventListener("click", function () {
      const modal = document.getElementById("auth-modal");
      const dropdown = document.getElementById("profile-dropdown");
      if (modal) modal.classList.remove("active");
      if (dropdown) dropdown.classList.remove("active");
    });
  }
});
