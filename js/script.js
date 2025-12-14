// Apply filter function
function applyFilters() {
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

    // Cek animal type (normalize to lowercase)
    if (checkedFilters.animal.length > 0) {
      const cardAnimal = (card.dataset.animal || "").toLowerCase();
      const selectedAnimals = checkedFilters.animal.map((a) => a.toLowerCase());
      if (!selectedAnimals.includes(cardAnimal)) {
        shouldShow = false;
      }
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
}

// Filter collapse/expand functionality
function setupFilterCollapse() {
  const filterToggles = document.querySelectorAll(".filter-toggle");
  const allOptions = document.querySelectorAll(".filter-options");

  // Initialize: ensure open groups have a usable max-height
  allOptions.forEach((opts) => {
    const header = opts.previousElementSibling;
    const toggle = header ? header.querySelector(".filter-toggle") : null;
    const isExpanded =
      toggle && toggle.getAttribute("aria-expanded") === "true";
    if (isExpanded && !opts.classList.contains("collapsed")) {
      opts.style.maxHeight = "2000px";
    }
  });

  filterToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      const filterGroup = this.closest(".filter-group");
      const filterOptions = filterGroup
        ? filterGroup.querySelector(".filter-options")
        : null;

      if (!filterOptions) return;

      const isCollapsed = filterOptions.classList.contains("collapsed");

      if (isCollapsed) {
        // Expanding: remove collapsed, set to large max-height
        filterOptions.classList.remove("collapsed");
        filterOptions.style.maxHeight = "2000px";
        this.setAttribute("aria-expanded", "true");
      } else {
        // Collapsing: set to exact current height, then animate to 0
        const currentHeight = filterOptions.scrollHeight;
        filterOptions.style.maxHeight = currentHeight + "px";
        // Force reflow
        filterOptions.offsetHeight;
        requestAnimationFrame(() => {
          filterOptions.style.maxHeight = "0px";
        });
        const onCollapseEnd = (evt) => {
          if (evt.propertyName === "max-height") {
            filterOptions.classList.add("collapsed");
            filterOptions.removeEventListener("transitionend", onCollapseEnd);
          }
        };
        filterOptions.addEventListener("transitionend", onCollapseEnd);
        this.setAttribute("aria-expanded", "false");
      }
    });
  });
}

// Set active navbar link based on current page
function setActiveNavLink() {
  const currentPath = window.location.pathname;
  const currentPage = currentPath.split("/").pop() || "index.html";
  const navLinks = document.querySelectorAll(".navbar-nav a");

  console.log("Current page:", currentPage); // Debug
  console.log("Current path:", currentPath); // Debug

  navLinks.forEach((link) => {
    const linkText = link.textContent.trim();

    link.classList.remove("active");

    // Home page
    if (
      currentPage === "index.html" ||
      currentPage === "index.php" ||
      currentPage === "" ||
      currentPage === "PetResQ"
    ) {
      if (!currentPath.includes("sistemadopt") && linkText === "Home") {
        link.classList.add("active");
        console.log("Set active: Home");
      }
    }
    // Adopt pages
    else if (currentPage === "adopt.html" || currentPage === "adopt.php") {
      if (linkText === "Adopt") {
        link.classList.add("active");
        console.log("Set active: Adopt");
      }
    }
    // Animal profile also highlights Adopt
    else if (
      currentPage === "animalprofile.html" ||
      currentPage === "animalprofile.php"
    ) {
      if (linkText === "Adopt") {
        link.classList.add("active");
        console.log("Set active: Adopt (from profile)");
      }
    }
    // Sistem adopt pages
    else if (currentPath.includes("sistemadopt")) {
      if (linkText === "Adopt") {
        link.classList.add("active");
        console.log("Set active: Adopt (from sistemadopt)");
      }
    }
    // Care Guides pages: highlight Care Guides parent link
    else if (currentPath.includes("careguides/")) {
      const parentLink = document.querySelector(
        ".navbar-dropdown .care-guides-link"
      );
      if (parentLink) {
        parentLink.classList.add("active");
        // Also mark dropdown as active for consistent styling
        const dropdown = parentLink.closest(".navbar-dropdown");
        if (dropdown) dropdown.classList.add("active");
        console.log("Set active: Care Guides");
      }
    }
    // Rehome pages
    else if (
      currentPage === "rehome.html" ||
      currentPage === "submit_rehome.php" ||
      currentPage === "rehome.php"
    ) {
      if (linkText === "Rehome") {
        link.classList.add("active");
        console.log("Set active: Rehome");
      }
    }
  });
}

// Real-time filter on checkbox change
document.addEventListener("DOMContentLoaded", function () {
  const filterCheckboxes = document.querySelectorAll(
    '.filter-option input[type="checkbox"]'
  );

  filterCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", applyFilters);
  });

  // Setup filter collapse
  setupFilterCollapse();

  // Set active navbar link
  setActiveNavLink();
});

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

// Hide Apply Filter Button (no longer needed)
const applyFilterBtn = document.querySelector(".apply-filter-btn");
if (applyFilterBtn) {
  applyFilterBtn.style.display = "none";
}

// Function to setup modal event listeners (called on DOMContentLoaded)
function setupModalListeners() {
  const modal = document.getElementById("auth-modal");
  const closeModalBtn = document.getElementById("close-modal");
  const switchTabLinks = document.querySelectorAll(".switch-tab");
  const loginImage = document.querySelector(".modal-image-login");
  const registerImage = document.querySelector(".modal-image-register");
  const modalTitle = document.getElementById("modal-title");

  //close modal event
  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", function () {
      if (modal) {
        modal.classList.remove("active");
        modal.style.display = "";
        modal.style.zIndex = "";
        modal.style.visibility = "";
        modal.style.opacity = "";
        const modalContent = modal.querySelector(".modal-content");
        if (modalContent) {
          modalContent.style.display = "";
        }
      }
    });
  }

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        modal.classList.remove("active");
        modal.style.display = "";
        modal.style.zIndex = "";
        modal.style.visibility = "";
        modal.style.opacity = "";
        const modalContent = modal.querySelector(".modal-content");
        if (modalContent) {
          modalContent.style.display = "";
        }
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
      const targetTab = document.getElementById(tabName + "-tab");
      if (targetTab) {
        targetTab.classList.add("active");
      }

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
}

// Check user login status on page load
function checkUserLogin() {
  const sessionPath = getPhpPath("check_session.php");
  fetch(sessionPath)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("Response is not JSON");
      }
      return response.json();
    })
    .then((data) => {
      if (data.is_logged_in) {
        displayUserProfile(data.user_name, data.user_email);
        // If admin, inject Admin link into navbar; otherwise ensure it's hidden
        if (data.role === "admin") {
          addAdminNavLink();
        } else {
          removeAdminNavLink();
        }
        // For logged-in users, add My Submissions link to account dropdown
        addUserSubmissionsDropdownItem();
      } else {
        displayLoginButton();
        // Not logged in: ensure Admin link isn't shown
        removeAdminNavLink();
      }
    })
    .catch((error) => {
      console.error("Error checking login:", error);
      // Fallback: show login button if check fails
      displayLoginButton();
      removeAdminNavLink();
    });
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
  const clonedProfile = userProfile.cloneNode(true);
  userProfile.parentNode.replaceChild(clonedProfile, userProfile);

  const newUserProfile = document.getElementById("user-profile");
  if (newUserProfile) {
    newUserProfile.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Login button clicked");
      openLoginModal(e);
    });
    console.log("Login button event listener attached");
  } else {
    console.error("Failed to find user-profile after cloning");
  }
}

// Open login modal
function openLoginModal(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const modal = document.getElementById("auth-modal");
  if (modal) {
    console.log("Opening login modal");
    modal.classList.add("active");
    modal.style.display = "flex";
    modal.style.zIndex = "10000";
    modal.style.visibility = "visible";
    modal.style.opacity = "1";

    // Ensure modal content is visible
    const modalContent = modal.querySelector(".modal-content");
    if (modalContent) {
      modalContent.style.display = "flex";
    }

    console.log("Modal should be visible now");
  } else {
    console.error("Auth modal not found in DOM");
    alert("Login modal not found. Please refresh the page.");
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

// Get the correct path to PHP files based on current location
function getPhpPath(phpFile) {
  // Check if we're in a subdirectory by looking at the current pathname
  const pathSegments = window.location.pathname
    .split("/")
    .filter((seg) => seg !== "");

  console.log("Current path segments:", pathSegments);
  console.log("Requested PHP file:", phpFile);

  // pathSegments for /pet-rpl/proyek/index.php would be: ["pet-rpl", "proyek", "index.php"]
  // pathSegments for /pet-rpl/proyek/adopt/adopt.php would be: ["pet-rpl", "proyek", "adopt", "adopt.php"]

  // If we're in proyek/ root (index.php), pathSegments.length would be 3
  // If we're in a subdirectory like adopt/, pathSegments.length would be 4 or more
  if (pathSegments.length > 3) {
    // We're in a subdirectory, need to go up one level
    const result = `../${phpFile}`;
    console.log("Subdirectory detected, using path:", result);
    return result;
  }
  // We're in the root directory (proyek/)
  console.log("Root directory, using path:", phpFile);
  return phpFile;
}

// Handle form submissions with AJAX
function handleFormSubmit(formId, phpFile) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    const errorDiv = document.getElementById("form-error");
    const correctPath = getPhpPath(phpFile);

    fetch(correctPath, {
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

// Animal card click handler
function setupAnimalCardListeners() {
  const animalCards = document.querySelectorAll(".animal-card");

  animalCards.forEach((card, index) => {
    card.style.cursor = "pointer";
    card.addEventListener("click", function () {
      // Use index as animal ID (1-based)
      const animalId = index + 1;
      // Navigate to animal profile with ID
      window.location.href = `animalprofile.php?id=${animalId}`;
    });

    // Add hover effect
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px)";
      this.style.transition = "transform 0.3s ease";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  // Setup modal listeners
  setupModalListeners();

  // Check login status
  checkUserLogin();

  // Setup form submissions
  handleFormSubmit("login-tab", "login.php");
  handleFormSubmit("register-tab", "register.php");

  // Setup animal card listeners (only on adopt.html)
  setupAnimalCardListeners();

  // Setup logout button
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const logoutPath = getPhpPath("logout.php");
      window.location.href = logoutPath;
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

  // Guard Rehome: require login, otherwise show login modal
  const rehomeLink = Array.from(
    document.querySelectorAll(".navbar-nav a")
  ).find((a) => a.textContent.trim().toLowerCase() === "rehome");
  if (rehomeLink) {
    rehomeLink.addEventListener("click", function (e) {
      // Prevent immediate navigation until we check session
      e.preventDefault();
      e.stopPropagation();

      const sessionPath = getPhpPath("check_session.php");
      fetch(sessionPath)
        .then((response) => {
          if (!response.ok) throw new Error("Session check failed");
          return response.json();
        })
        .then((data) => {
          if (data && data.is_logged_in) {
            // Proceed to Rehome page
            window.location.href = getPhpPath("rehome/rehome.html");
          } else {
            // Not logged in: open login modal
            openLoginModal();
          }
        })
        .catch((err) => {
          console.error("Error checking session:", err);
          // Fallback: show login modal
          openLoginModal();
        });
    });
  }

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

// Adds an Admin link to the navbar if not already present
function addAdminNavLink() {
  const nav = document.querySelector(".navbar-nav");
  if (!nav) return;
  // Avoid duplicating the link
  const existing = Array.from(nav.querySelectorAll("a")).find(
    (a) => a.textContent.trim().toLowerCase() === "admin"
  );
  if (existing) return;

  const link = document.createElement("a");
  // Point to guarded Admin panel entry
  link.href = getPhpPath("Admin PetResQ/index.php");
  link.textContent = "Admin";
  nav.appendChild(link);
}

// Removes Admin link if present (for non-admin users)
function removeAdminNavLink() {
  const nav = document.querySelector(".navbar-nav");
  if (!nav) return;
  const adminLink = Array.from(nav.querySelectorAll("a")).find(
    (a) => a.textContent.trim().toLowerCase() === "admin"
  );
  if (adminLink) {
    adminLink.remove();
  }
}

// Adds a My Submissions link for logged-in users
function addUserSubmissionsDropdownItem() {
  const dropdown = document.getElementById("profile-dropdown");
  if (!dropdown) return;
  // Avoid duplicate by checking existing items
  const exists = Array.from(dropdown.querySelectorAll(".dropdown-item")).find(
    (a) => a.textContent.trim().toLowerCase() === "my submissions"
  );
  if (exists) return;

  // Insert before the logout item
  const logoutItem = document.getElementById("logout-btn");
  const item = document.createElement("a");
  item.href = getPhpPath("user/submissions.php");
  item.className = "dropdown-item";
  item.textContent = "My Submissions";
  if (logoutItem && logoutItem.parentNode === dropdown) {
    dropdown.insertBefore(item, logoutItem);
    // Add divider between My Submissions and Logout
    const divider = document.createElement("hr");
    divider.className = "dropdown-divider";
    dropdown.insertBefore(divider, logoutItem);
  } else {
    dropdown.appendChild(item);
  }
}
