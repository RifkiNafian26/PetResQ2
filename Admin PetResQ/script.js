// =========================================================
// --- LOGIN SYSTEM FUNCTIONS ---
// =========================================================

function setupModalListeners() {
  const modal = document.getElementById("auth-modal");
  const closeBtn = document.getElementById("close-modal");
  const userProfile = document.getElementById("user-profile");
  const switchTabs = document.querySelectorAll(".switch-tab");

  if (userProfile) {
    userProfile.addEventListener("click", () => {
      const profileDropdown = document.getElementById("profile-dropdown");
      if (profileDropdown && profileDropdown.classList.contains("active")) {
        profileDropdown.classList.remove("active");
      } else {
        modal.classList.add("active");
      }
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      modal.classList.remove("active");
    });
  }

  switchTabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
      e.preventDefault();
      const targetTab = tab.getAttribute("data-tab");
      const loginTab = document.getElementById("login-tab");
      const registerTab = document.getElementById("register-tab");
      const modalTitle = document.getElementById("modal-title");

      if (targetTab === "login") {
        loginTab.classList.add("active");
        registerTab.classList.remove("active");
        document.querySelector(".modal-image-login").classList.add("active");
        document
          .querySelector(".modal-image-register")
          .classList.remove("active");
        modalTitle.textContent = "Login";
      } else {
        registerTab.classList.add("active");
        loginTab.classList.remove("active");
        document.querySelector(".modal-image-register").classList.add("active");
        document.querySelector(".modal-image-login").classList.remove("active");
        modalTitle.textContent = "Register";
      }
    });
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
}

function handleFormSubmit(formId, phpFile) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    const errorDiv = document.getElementById("form-error");

    fetch("../" + phpFile, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          setTimeout(() => {
            location.reload();
          }, 500);
        } else {
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

function checkUserLogin() {
  fetch("../check_session.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.is_logged_in) {
        displayUserProfile(data.user_name, data.user_email);
        if (data.role === "admin") {
          initializeDashboard();
        } else {
          document.body.innerHTML = "<h1>Access Denied: Admin only</h1>";
        }
      } else {
        displayLoginButton();
      }
    })
    .catch((error) => console.error("Error checking login:", error));
}

function displayUserProfile(userName, userEmail) {
  const userProfile = document.getElementById("user-profile");
  if (!userProfile) return;

  const userName_span = userProfile.querySelector(".user-name");
  const userInitial = userProfile.querySelector(".user-initial");
  const profileIcon = userProfile.querySelector(".profile-icon");

  if (profileIcon) profileIcon.style.display = "none";
  if (userName_span) userName_span.style.display = "none";

  if (userInitial) {
    userInitial.textContent = userName;
    userInitial.style.display = "inline-block";
  }

  const dropdownName = document.getElementById("dropdown-name");
  const dropdownEmail = document.getElementById("dropdown-email");
  if (dropdownName) dropdownName.textContent = userName;
  if (dropdownEmail) dropdownEmail.textContent = userEmail;

  const modal = document.getElementById("auth-modal");
  if (modal) {
    modal.classList.remove("active");
  }

  const newUserProfile = userProfile.cloneNode(true);
  userProfile.parentNode.replaceChild(newUserProfile, userProfile);

  newUserProfile.addEventListener("click", () => {
    const profileDropdown = document.getElementById("profile-dropdown");
    if (profileDropdown) {
      profileDropdown.classList.toggle("active");
    }
  });
}

function displayLoginButton() {
  const userProfile = document.getElementById("user-profile");
  if (!userProfile) return;

  const userName_span = userProfile.querySelector(".user-name");
  const userInitial = userProfile.querySelector(".user-initial");
  const profileIcon = userProfile.querySelector(".profile-icon");

  if (profileIcon) profileIcon.style.display = "block";
  if (userName_span) userName_span.style.display = "inline";

  if (userInitial) {
    userInitial.style.display = "none";
  }

  const userProfile_new = userProfile.cloneNode(true);
  userProfile.parentNode.replaceChild(userProfile_new, userProfile);

  userProfile_new.addEventListener("click", () => {
    const modal = document.getElementById("auth-modal");
    if (modal) {
      modal.classList.add("active");
    }
  });
}

// =========================================================
// --- INITIAL DUMMY DATA (DIKOSONGKAN) ---
// =========================================================
let mockReports = [];
let selectedDateRange = { start: null, end: null };
let selectedDay = null; // Store the selected day from calendar
let isDragging = false;
let mockUsers = [];
let mockAnimals = [];
let mockSubmissions = [];

// Make selectedDateRange and currentDate accessible globally
window.selectedDateRange = selectedDateRange;
window.selectedDay = selectedDay;

// =========================================================
// --- FUNGSI UTAMA DASHBOARD ---
// =========================================================
  // --- LOAD ANIMALS FROM BACKEND ---
  async function loadAnimalsFromBackend() {
    try {
      const response = await fetch("../admin/animals_api.php", {
        credentials: "include",
      });
      
      if (!response.ok) {
        throw new Error("Failed to load animals data");
      }
      
      const data = await response.json();
      
      // Update summary
      const summaryTotal = document.getElementById("summary-total");
      const summaryDogs = document.getElementById("summary-dogs");
      const summaryCats = document.getElementById("summary-cats");
      const summaryRabbits = document.getElementById("summary-rabbits");
      const animalsCount = document.getElementById("animals-count");
      
      if (summaryTotal) summaryTotal.textContent = data.summary.total || 0;
      if (summaryDogs) summaryDogs.textContent = data.summary.dogs || 0;
      if (summaryCats) summaryCats.textContent = data.summary.cats || 0;
      if (summaryRabbits) summaryRabbits.textContent = data.summary.rabbits || 0;
      if (animalsCount) {
        animalsCount.textContent = `Showing ${data.animals.length} of ${data.summary.total} animals`;
      }
      
      // Render animal cards
      renderAnimalCards(data.animals);
      
      return data;
    } catch (error) {
      console.error("Error loading animals data:", error);
      const animalsGrid = document.getElementById("animals-grid");
      if (animalsGrid) {
        animalsGrid.innerHTML = `
          <div style="grid-column: 1 / -1; text-align: center; color: #888; padding: 40px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
            Error loading animals. Please try again.
          </div>
        `;
      }
      return null;
    }
  }

  // Store animals data globally for modal access
  let currentAnimalsData = [];

  // --- RENDER ANIMAL CARDS ---
  function renderAnimalCards(animals) {
    const animalsGrid = document.getElementById("animals-grid");
    if (!animalsGrid) return;
    
    // Store animals data globally
    currentAnimalsData = animals || [];
    
    if (!animals || animals.length === 0) {
      animalsGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; color: #888; padding: 40px;">
          <i class="fas fa-paw" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
          No animals found.
        </div>
      `;
      return;
    }
    
    // Use data URI for placeholder image to avoid 404 loops
    const placeholderImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23f0f0f0' width='200' height='200'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-family='Arial' font-size='14'%3ENo Image%3C/text%3E%3C/svg%3E";
    
    let cardsHTML = '';
    animals.forEach(animal => {
      const photoUrl = animal.main_photo ? `../${animal.main_photo}` : placeholderImage;
      // Determine status class and display text
      let statusClass, statusText, statusBgColor, statusTextColor;
      const animalStatus = (animal.status || 'Available').toString().trim();
      
      if (animalStatus.toLowerCase() === 'adopted') {
        statusClass = 'status-adopted';
        statusText = 'Adopted';
        statusBgColor = '#d9534f'; // Red
        statusTextColor = '#ffffff';
      } else if (animalStatus.toLowerCase() === 'available') {
        statusClass = 'status-approved';
        statusText = 'Available';
        statusBgColor = '#28a745'; // Green
        statusTextColor = '#ffffff';
      } else {
        statusClass = 'status-rejected';
        statusText = animalStatus;
        statusBgColor = '#6c757d'; // Gray
        statusTextColor = '#ffffff';
      }
      
      cardsHTML += `
        <div class="animal-card-admin" data-animal-id="${animal.id_hewan}" style="
          background: white;
          border-radius: 12px;
          padding: 15px;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
          cursor: pointer;
          transition: all 0.3s ease;
        ">
          <div style="position: relative; margin-bottom: 12px;">
            <img src="${photoUrl}" alt="${animal.namaHewan}" style="
              width: 100%;
              height: 200px;
              object-fit: cover;
              border-radius: 8px;
              background: #f0f0f0;
              ${animalStatus.toLowerCase() === 'adopted' ? 'opacity: 0.6; filter: grayscale(50%);' : ''}
            " onerror="this.onerror=null; this.src='${placeholderImage}'">
            <span class="${statusClass}" style="
              position: absolute;
              top: 8px;
              right: 8px;
              padding: 4px 10px;
              border-radius: 12px;
              font-size: 0.75em;
              font-weight: 600;
              background-color: ${statusBgColor};
              color: ${statusTextColor};
              z-index: 10;
            ">${statusText}</span>
          </div>
          <h4 style="
            margin: 0 0 8px 0;
            color: var(--color-primary-dark);
            font-size: 1.1em;
          ">${animal.namaHewan}</h4>
          <div style="
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            color: #666;
            font-size: 0.9em;
          ">
            <i class="fas fa-paw" style="color: var(--color-primary-light);"></i>
            <span>${animal.jenis}</span>
          </div>
          <div style="
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            color: #666;
            font-size: 0.9em;
          ">
            <i class="fas fa-tag" style="color: var(--color-primary-light);"></i>
            <span>${animal.breed || 'N/A'}</span>
          </div>
          <div style="
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.85em;
          ">
            <i class="fas fa-user" style="color: var(--color-primary-light);"></i>
            <span>${animal.uploaded_by || 'System'}</span>
          </div>
        </div>
      `;
    });
    
    animalsGrid.innerHTML = cardsHTML;
    
    // Add click handlers
    const animalCards = animalsGrid.querySelectorAll(".animal-card-admin");
    animalCards.forEach(card => {
      card.addEventListener("click", function(e) {
        e.stopPropagation();
        const animalId = this.getAttribute("data-animal-id");
        if (animalId) {
          showAnimalDetail(parseInt(animalId));
        }
      });
      
      // Add hover effect
      card.addEventListener("mouseenter", function() {
        this.style.transform = "translateY(-5px)";
        this.style.boxShadow = "0 4px 12px rgba(0, 0, 0, 0.15)";
      });
      
      card.addEventListener("mouseleave", function() {
        this.style.transform = "translateY(0)";
        this.style.boxShadow = "0 2px 8px rgba(0, 0, 0, 0.1)";
      });
    });
  }

  // --- SHOW ANIMAL DETAIL MODAL ---
  function showAnimalDetail(animalId) {
    // Use global animals data
    const animals = currentAnimalsData;
    if (!animals || animals.length === 0) {
      console.error("No animals data available");
      alert("Animal data not loaded. Please refresh the page.");
      return;
    }
    
    const animal = animals.find(a => a.id_hewan === animalId);
    if (!animal) {
      console.error("Animal not found with ID:", animalId);
      alert("Animal not found");
      return;
    }
    
    const modal = document.getElementById("animal-detail-modal");
    if (!modal) {
      console.error("Animal detail modal not found in DOM");
      return;
    }
    
    // Use data URI for placeholder image
    const placeholderImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23f0f0f0' width='200' height='200'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-family='Arial' font-size='14'%3ENo Image%3C/text%3E%3C/svg%3E";
    
    // Populate modal
    const idEl = document.getElementById("animal-detail-id");
    if (idEl) idEl.textContent = `#${animalId.toString().padStart(3, "0")}`;
    
    const photoEl = document.getElementById("animal-detail-photo");
    if (photoEl) {
      photoEl.src = animal.main_photo ? `../${animal.main_photo}` : placeholderImage;
      photoEl.onerror = function() {
        this.onerror = null; // Prevent infinite loop
        this.src = placeholderImage;
      };
    }
    
    const nameEl = document.getElementById("animal-detail-name");
    if (nameEl) nameEl.textContent = animal.namaHewan || "N/A";
    
    const typeEl = document.getElementById("animal-detail-type");
    if (typeEl) typeEl.textContent = animal.jenis || "N/A";
    
    const breedEl = document.getElementById("animal-detail-breed");
    if (breedEl) breedEl.textContent = animal.breed || "N/A";
    
    const genderEl = document.getElementById("animal-detail-gender");
    if (genderEl) genderEl.textContent = animal.gender || "N/A";
    
    const ageEl = document.getElementById("animal-detail-age");
    if (ageEl) ageEl.textContent = animal.age || "N/A";
    
    const colorEl = document.getElementById("animal-detail-color");
    if (colorEl) colorEl.textContent = animal.color || "N/A";
    
    const weightEl = document.getElementById("animal-detail-weight");
    if (weightEl) weightEl.textContent = animal.weight ? `${animal.weight} kg` : "N/A";
    
    const heightEl = document.getElementById("animal-detail-height");
    if (heightEl) heightEl.textContent = animal.height ? `${animal.height} cm` : "N/A";
    
    const statusEl = document.getElementById("animal-detail-status");
    if (statusEl) {
      const animalStatus = (animal.status || 'Available').toString().trim();
      let statusClass, statusText, statusBgColor, statusTextColor;
      
      if (animalStatus.toLowerCase() === 'adopted') {
        statusClass = 'status-adopted';
        statusText = 'Adopted';
        statusBgColor = '#d9534f';
        statusTextColor = '#ffffff';
      } else if (animalStatus.toLowerCase() === 'available') {
        statusClass = 'status-approved';
        statusText = 'Available';
        statusBgColor = '#28a745';
        statusTextColor = '#ffffff';
      } else {
        statusClass = 'status-rejected';
        statusText = animalStatus;
        statusBgColor = '#6c757d';
        statusTextColor = '#ffffff';
      }
      
      statusEl.innerHTML = `<span class="${statusClass}" style="
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: 600;
        background-color: ${statusBgColor};
        color: ${statusTextColor};
      ">${statusText}</span>`;
    }
    
    const uploadedByEl = document.getElementById("animal-detail-uploaded-by");
    if (uploadedByEl) uploadedByEl.textContent = animal.uploaded_by || "System";
    
    const descEl = document.getElementById("animal-detail-description");
    if (descEl) descEl.value = animal.description || "No description available.";
    
    // Show modal
    modal.style.display = "flex";
    modal.style.zIndex = "10000";
    modal.classList.add("active");
    
    console.log("Animal detail modal opened for:", animal.namaHewan);
  }

function initializeDashboard() {
  const today = new Date();
  let currentDate = today;
  
  // Make currentDate accessible globally
  window.currentDate = currentDate;

  const pageContent = document.getElementById("page-content");
  const navItems = document.querySelectorAll(".nav-menu li");
  const createReportModal = document.getElementById("create-report-modal");
  const reportDetailModal = document.getElementById("report-detail-modal");
  const submissionDetailModal = document.getElementById(
    "submission-detail-modal"
  );
  const logoutBtn = document.getElementById("logout-btn");
  const logoutModal = document.getElementById("logout-modal");
  const reportForm = document.getElementById("report-form");

  // Navbar logout button
  const navbarLogoutBtn = document.getElementById("navbar-logout-btn");
  if (navbarLogoutBtn) {
    navbarLogoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const logoutPath = "../logout.php";
      window.location.href = logoutPath;
    });
  }

  // User profile dropdown - already handled in checkUserLogin
  const profileDropdown = document.getElementById("profile-dropdown");
  document.addEventListener("click", (e) => {
    if (
      profileDropdown &&
      !document.getElementById("user-profile")?.contains(e.target) &&
      !profileDropdown.contains(e.target)
    ) {
      profileDropdown.classList.remove("active");
    }
  });

  // Feather icons
  feather.replace();

  // Klik Logo PetResQ kembali ke dashboard
  document.querySelector(".logo").addEventListener("click", () => {
    navigateTo("dashboard");
  });

  // Ensure modal exists in DOM on page load
  const checkModal = () => {
    const modal = document.getElementById("submission-detail-modal");
    if (!modal) {
      console.warn("Submission detail modal not found in DOM on page load");
    } else {
      console.log("Submission detail modal found in DOM");
      // Ensure modal is hidden by default
      modal.style.display = "none";
    }
  };
  
  // Check modal immediately and after DOM is ready
  checkModal();
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", checkModal);
  }
  
  // Setup listener untuk modal dengan event delegation
  document.addEventListener("click", (e) => {
    // Handle rehome submission card click (event delegation)
    const rehomeCard = e.target.closest(".rehome-submission-card");
    if (rehomeCard) {
      e.preventDefault();
      e.stopPropagation();
      const submissionId = rehomeCard.getAttribute("data-submission-id");
      if (submissionId) {
        console.log("Rehome card clicked, ID:", submissionId);
        openRehomeDetail(parseInt(submissionId));
        return;
      }
    }
    
    // Handle modal close
    if (e.target.classList.contains("modal-cancel") ||
        e.target.classList.contains("modal-close") ||
        (e.target.classList.contains("modal") && e.target.id !== "auth-modal")) {
      const modal = e.target.closest(".modal") || e.target;
      if (modal && modal.id !== "auth-modal") {
        modal.style.display = "none";
        modal.classList.remove("active");
      }
    }
    
    // Handle Create Report button click (event delegation)
    if (e.target.id === "create-report-btn" || e.target.closest("#create-report-btn")) {
      e.preventDefault();
      e.stopPropagation();
      
      console.log("Create Report button clicked");
      
      const modal = document.getElementById("create-report-modal");
      if (!modal) {
        console.error("Create report modal not found in DOM");
        return;
      }
      
      console.log("Modal found, opening...");
      modal.style.display = "flex";
      modal.style.zIndex = "10000";
      modal.classList.add("active");
      
      // Get selected date range or use current date
      let startDate, endDate;
      const formatDate = (date) => {
        if (!date || !(date instanceof Date)) return "";
        return `${date.getFullYear()}-${(date.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
      };
      
      // Get current date range from global scope or local
      const dateRange = window.selectedDateRange || selectedDateRange;
      const currentDateForModal = window.currentDate || currentDate || new Date();
      
      if (dateRange.start && dateRange.end) {
        const start = dateRange.start < dateRange.end 
          ? dateRange.start 
          : dateRange.end;
        const end = dateRange.start < dateRange.end 
          ? dateRange.end 
          : dateRange.start;
        
        startDate = formatDate(start);
        endDate = formatDate(end);
      } else {
        const formattedDate = formatDate(currentDateForModal);
        startDate = formattedDate;
        endDate = formattedDate;
      }
      
      const startDateInput = document.getElementById("report-start-date");
      const endDateInput = document.getElementById("report-end-date");
      if (startDateInput) startDateInput.value = startDate;
      if (endDateInput) endDateInput.value = endDate;
      
      // Reset form
      const form = document.getElementById("report-form");
      if (form) {
        const descTextarea = document.getElementById("report-description");
        if (descTextarea) descTextarea.value = "";
        const reportType = document.getElementById("report-type");
        if (reportType) reportType.value = "Daily";
      }
    }
  });

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      logoutModal.style.display = "flex";
    });
  }

  // Logout confirmation
  const confirmLogoutBtn = document.getElementById("confirm-logout");
  if (confirmLogoutBtn) {
    confirmLogoutBtn.addEventListener("click", function () {
      if (logoutModal) logoutModal.style.display = "none";
      const logoutPath = "../logout.php";
      window.location.href = logoutPath;
    });
  }

  // --- LOGIKA FORM LAPORAN BARU (Event Delegation) ---
  // Use event delegation to handle dynamically created forms
  let isSubmittingReport = false; // Flag to prevent duplicate submissions
  
  document.addEventListener("submit", async function (e) {
    if (e.target.id !== "report-form") return;
      e.preventDefault();

    // Prevent duplicate submissions
    if (isSubmittingReport) {
      console.log("Report submission already in progress, ignoring duplicate submit");
      return;
    }

    const startDate = document.getElementById("report-start-date")?.value;
    const endDate = document.getElementById("report-end-date")?.value;
    const reportType = document.getElementById("report-type")?.value;
    const description = document.getElementById("report-description")?.value;

    if (!startDate || !endDate || !description) {
      alert("Please fill all required fields");
      return;
    }
    
    // Set flag to prevent duplicate submissions
    isSubmittingReport = true;

    try {
      const response = await fetch("../admin/reports_api.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
          report_type: reportType,
          start_date: startDate,
          end_date: endDate,
          description: description,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        // Close modal
        const modal = document.getElementById("create-report-modal");
        if (modal) {
          modal.style.display = "none";
          modal.classList.remove("active");
        }
        
        // Reset form
        const form = document.getElementById("report-form");
        if (form) form.reset();

        // Reload reports from backend
        await loadReportsFromBackend();
        
        // Update metrics
        updateReportsMetrics();

        // Update view with current selection
        const reportsBody = document.getElementById("reports-body");
        if (reportsBody) {
          // Check if there's a date range selected
          const dateRange = window.selectedDateRange || selectedDateRange;
          if (dateRange && dateRange.start && dateRange.end && 
              dateRange.start.getTime() !== dateRange.end.getTime()) {
            const start = dateRange.start < dateRange.end 
              ? dateRange.start 
              : dateRange.end;
            const end = dateRange.start < dateRange.end 
              ? dateRange.end 
              : dateRange.start;
            updateReportsViewWithRange(start, end);
          } else {
            // Use the date from the form (the date that was selected/entered)
            const formStartDate = new Date(startDate + 'T00:00:00');
        updateReportsView(
              formStartDate.getDate(),
              formStartDate.getMonth(),
              formStartDate.getFullYear()
            );
            
            // Update currentDate and selectedDay to the selected date so calendar shows it
            window.currentDate = formStartDate;
            currentDate = formStartDate;
            window.selectedDay = formStartDate.getDate();
            selectedDay = formStartDate.getDate();
          }
          
          // Re-render calendar to show updated data
          if (window.currentDate) {
            renderCalendar(window.currentDate);
          } else if (currentDate) {
        renderCalendar(currentDate);
      }
        }

        // Show success message
        alert(
          `Report ID ${data.report_id
            .toString()
            .padStart(3, "0")} (${reportType}) berhasil dibuat!`
        );
      } else {
        alert("Error: " + (data.message || "Failed to create report"));
      }
    } catch (error) {
      console.error("Error creating report:", error);
      alert("Error creating report. Please try again.");
    } finally {
      // Reset flag after submission completes
      isSubmittingReport = false;
    }
  });
  // --- FUNGSI KLIK ROW REPORT (MENAMPILKAN DETAIL) ---
  function showReportDetail(reportId) {
    const report = mockReports.find((r) => r.id === reportId);
    if (!report) return;

    const reportCreationDate = new Date(report.created_at || report.date);

    document.getElementById("detail-report-id").textContent = `(ID: ${report.id
      .toString()
      .padStart(3, "0")})`;
    document.getElementById("detail-type").textContent = report.report_type || report.type || "Daily";
    document.getElementById("detail-range").textContent = report.range || 
      (report.start_date && report.end_date ? `${report.start_date} to ${report.end_date}` : "-");
    document.getElementById("detail-created-date").textContent =
      reportCreationDate.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
      });
    const status = (report.status || "Pending").charAt(0).toUpperCase() + (report.status || "Pending").slice(1).toLowerCase();
    document.getElementById("detail-status").textContent = status;
    document.getElementById("detail-description").value = report.description || report.desc || "";

    reportDetailModal.style.display = "flex";
  }

  window.showReportDetail = showReportDetail;

  // --- FUNGSI HAPUS LAPORAN ---
  window.deleteReport = function (reportId) {
    if (
      confirm(
        `Are you sure you want to delete Report ID ${reportId
          .toString()
          .padStart(3, "0")}?`
      )
    ) {
      mockReports = mockReports.filter((r) => r.id !== reportId);
      alert(`Report ID ${reportId.toString().padStart(3, "0")} deleted.`);
      updateReportsView(
        currentDate.getDate(),
        currentDate.getMonth(),
        currentDate.getFullYear()
      );
      renderCalendar(currentDate);
      navigateTo("system-reports");
    }
  };
  // --- FUNGSI EDIT LAPORAN ---
  window.editReport = function (reportId) {
    const report = mockReports.find((r) => r.id === reportId);
    if (!report) return;

    const newDesc = prompt(
      `Edit Description for Report ID ${report.id
        .toString()
        .padStart(3, "0")}:`,
      report.desc
    );
    if (newDesc !== null) {
      report.desc = newDesc;

      let validStatus = ["Done", "Pending", "Recap", "Cancelled"];
      let newStatus = prompt(
        `Edit Status (${validStatus.join(", ")}) for Report ID ${report.id
          .toString()
          .padStart(3, "0")}:`,
        report.status
      );

      if (newStatus !== null && validStatus.includes(newStatus)) {
        report.status = newStatus;
      } else if (newStatus !== null) {
        alert("Status tidak valid. Menggunakan status lama.");
      }

      alert(`Report ID ${report.id.toString().padStart(3, "0")} updated.`);
      updateReportsView(
        currentDate.getDate(),
        currentDate.getMonth(),
        currentDate.getFullYear()
      );
      navigateTo("system-reports");
    }
  };

  // --- FUNGSI SUBMISSION ---
  window.showSubmissionDetail = function (submissionId) {
    try {
      const submission = mockSubmissions.find((s) => s.id === submissionId);
      if (!submission) {
        console.warn("Submission not found:", submissionId);
        return;
      }

      const modalEl = document.getElementById("submission-detail-modal");
      if (!modalEl) {
        console.error("Submission detail modal not found in DOM");
        return;
      }

      document.getElementById(
        "submission-detail-id"
      ).textContent = `#${submission.id.toString().padStart(3, "0")}`;
      document.getElementById("submission-adopter-name").textContent =
        submission.adopterName || "-";
      document.getElementById("submission-adopter-email").textContent =
        submission.adopterEmail || "-";
      document.getElementById("submission-adopter-phone").textContent =
        submission.adopterPhone || "-";
      document.getElementById("submission-animal-name").textContent =
        submission.animalName || "-";

      const addrEl = document.getElementById("submission-address");
      if (addrEl) {
        addrEl.textContent = `${submission.address || ""}${
          submission.postcode ? " (" + submission.postcode + ")" : ""
        }`;
      }

      document.getElementById("submission-date").textContent = submission.date
        ? new Date(submission.date).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
          })
        : "-";

      const statusSpan = document.getElementById("submission-status-display");
      statusSpan.textContent = submission.status || "Pending";
      statusSpan.className =
        submission.status === "Approved"
          ? "status-approved"
          : submission.status === "Rejected"
          ? "status-rejected"
          : "status-pending";

      const reasonEl = document.getElementById("submission-reason");
      if (reasonEl) reasonEl.value = submission.reason || "";

      const gardenEl = document.getElementById("submission-garden");
      if (gardenEl) gardenEl.textContent = submission.hasGarden ? "Yes" : "No";
      const livingEl = document.getElementById("submission-living");
      if (livingEl) livingEl.textContent = submission.living || "-";

      // Display home photos
      const photosBox = document.getElementById("submission-home-photos");
      if (photosBox) {
        photosBox.innerHTML = "";
        const homePhotos = submission.home_photos || [];
        console.log("Home photos for submission:", submission.id, "Photos array:", homePhotos, "Type:", typeof homePhotos, "Is array:", Array.isArray(homePhotos));
        if (!homePhotos || homePhotos.length === 0) {
          photosBox.innerHTML = '<div style="color: #888; padding: 12px;">No home photos submitted.</div>';
        } else {
          homePhotos.forEach((photoPath) => {
            if (photoPath) {
              const img = document.createElement("img");
              // Path dari database: "uploads/adoption/home_photos/filename.jpg"
              // Path relatif dari Admin PetResQ/index.php: "../uploads/adoption/home_photos/filename.jpg"
              // Handle escaped backslashes from JSON
              const cleanPath = photoPath.replace(/\\\//g, '/');
              const fullPath = cleanPath.startsWith('uploads/') ? `../${cleanPath}` : cleanPath;
              img.src = fullPath;
              img.alt = "Home Photo";
              img.style.cssText = `
                width: 100%;
                height: 150px;
                object-fit: cover;
                border-radius: 8px;
                border: 2px solid var(--color-border);
                cursor: pointer;
                transition: transform 0.2s;
              `;
              img.onerror = function() {
                console.error("Failed to load image:", this.src);
                this.style.display = 'none';
              };
              img.onload = function() {
                console.log("Successfully loaded image:", this.src);
              };
              img.onmouseover = function() {
                this.style.transform = 'scale(1.05)';
              };
              img.onmouseout = function() {
                this.style.transform = 'scale(1)';
              };
              // Click to view full size
              img.onclick = function() {
                const modal = document.createElement("div");
                modal.style.cssText = `
                  position: fixed;
                  top: 0;
                  left: 0;
                  width: 100%;
                  height: 100%;
                  background: rgba(0,0,0,0.9);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  z-index: 10001;
                  cursor: pointer;
                `;
                const fullImg = document.createElement("img");
                fullImg.src = this.src;
                fullImg.style.cssText = `
                  max-width: 90%;
                  max-height: 90%;
                  border-radius: 8px;
                `;
                modal.appendChild(fullImg);
                modal.onclick = function() {
                  document.body.removeChild(modal);
                };
                document.body.appendChild(modal);
              };
              photosBox.appendChild(img);
            }
          });
        }
      }

      // Show Home Photos section for adopt submissions
      const homePhotosSection = document.getElementById('home-photos-section');
      if (homePhotosSection) {
        homePhotosSection.style.display = 'block';
      }
      
      // Hide rehome approve/reject buttons (this is for adopt submission)
      const approveBtn = document.getElementById("modal-approve-btn");
      const rejectBtn = document.getElementById("modal-reject-btn");
      if (approveBtn) approveBtn.style.display = "none";
      if (rejectBtn) rejectBtn.style.display = "none";

      const extraBox = document.getElementById("submission-extra-details");
      if (extraBox) {
        extraBox.innerHTML = "";
        const details = submission.details || {};
        const keys = Object.keys(details);
        if (keys.length === 0) {
          extraBox.textContent = "No additional details.";
        } else {
          const rows = keys
            .map((k) => {
              const val = details[k];
              const label = k.replace(/_/g, " ");
              return `<div><strong>${label}:</strong> ${String(val)}</div>`;
            })
            .join("");
          extraBox.innerHTML = rows;
        }
      }

      // Ensure modal renders above everything and is visible
      modalEl.style.display = "flex";
      modalEl.style.zIndex = 1000;
      modalEl.classList.add("active");
      // Focus the modal for accessibility
      if (modalEl.focus) modalEl.focus();
    } catch (err) {
      console.error("showSubmissionDetail error:", err);
    }
  };

  // Global ESC to close any open modal
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal").forEach((m) => {
        if (m.style.display === "flex") {
          m.style.display = "none";
          m.classList.remove("active");
        }
      });
    }
  });

  window.approveSubmission = function (submissionId) {
    const submission = mockSubmissions.find((s) => s.id === submissionId);
    if (!submission) return;

    if (
      confirm(
        `Approve submission from ${submission.adopterName} for ${submission.animalName}?`
      )
    ) {
      fetch("../admin/submissions_update.php", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id: submissionId, action: "approve" }),
      })
        .then((r) => r.json())
        .then((json) => {
          if (json.success) {
            submission.status = "Approved";
            alert("Submission approved and saved.");
            loadSubmissionsFromBackend().then(() =>
              renderSubmissions(currentSubmissionFilter)
            );
          } else {
            alert("Failed to approve: " + (json.message || "Unknown error"));
          }
        })
        .catch((err) => {
          console.warn("Approve error:", err);
          alert("Network/Server error while approving submission");
        });
    }
  };

  window.rejectSubmission = function (submissionId) {
    const submission = mockSubmissions.find((s) => s.id === submissionId);
    if (!submission) return;

    if (confirm(`Reject submission from ${submission.adopterName}?`)) {
      fetch("../admin/submissions_update.php", {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id: submissionId, action: "reject" }),
      })
        .then((r) => r.json())
        .then((json) => {
          if (json.success) {
            submission.status = "Rejected";
            alert("Submission rejected and saved.");
            loadSubmissionsFromBackend().then(() =>
              renderSubmissions(currentSubmissionFilter)
            );
          } else {
            alert("Failed to reject: " + (json.message || "Unknown error"));
          }
        })
        .catch((err) => {
          console.warn("Reject error:", err);
          alert("Network/Server error while rejecting submission");
        });
    }
  };

  let currentSubmissionFilter = "all";

  function renderSubmissions(filter = "all") {
    currentSubmissionFilter = filter;
    const grid = document.getElementById("submissions-grid");
    if (!grid) return;

    let filtered = mockSubmissions;
    if (filter === "pending") {
      filtered = mockSubmissions.filter((s) => s.status === "Pending");
    } else if (filter === "approved") {
      filtered = mockSubmissions.filter((s) => s.status === "Approved");
    } else if (filter === "rejected") {
      filtered = mockSubmissions.filter((s) => s.status === "Rejected");
    }

    if (filtered.length === 0) {
      grid.innerHTML = `
                <div class="empty-submissions" style="grid-column: 1 / -1;">
                    <i class="fas fa-clipboard-list"></i>
                    <p style="font-size: 1.1em;">No submissions found for "${filter}" filter.</p>
                    <p style="margin-top: 10px; color: #aaa;">Submission forms akan muncul di sini setelah koneksi database.</p>
                </div>
            `;
      return;
    }

    grid.innerHTML = filtered
      .map((submission) => {
        let statusClass =
          submission.status === "Approved"
            ? "status-approved"
            : submission.status === "Rejected"
            ? "status-rejected"
            : "status-pending";
        let actionButtons = "";

        if (submission.status === "Pending") {
          actionButtons = `
                    <div class="submission-actions">
                        <button class="action-btn btn-approve" onclick="approveSubmission(${submission.id})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="action-btn btn-reject" onclick="rejectSubmission(${submission.id})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                `;
        }

        return `
          <div class="submission-card" style="cursor:pointer;" onclick="showSubmissionDetail(${
            submission.id
          })">
                    <div class="submission-card-header">
                        <div class="submission-id">#${submission.id
                          .toString()
                          .padStart(3, "0")}</div>
                        <span class="${statusClass}">${submission.status}</span>
                    </div>
                    <div class="submission-card-body">
                        <div class="submission-info">
                            <i class="fas fa-user"></i>
                            <span>${submission.adopterName}</span>
                        </div>
                        <div class="submission-info">
                            <i class="fas fa-paw"></i>
                            <span>${submission.animalName}</span>
                        </div>
                        <div class="submission-info">
                          <i class="fas fa-envelope"></i>
                          <span>${submission.adopterEmail}</span>
                        </div>
                        
                    </div>
                    <div class="submission-card-footer">
                        <div class="submission-date">
                            <i class="fas fa-calendar"></i> ${new Date(
                              submission.date
                            ).toLocaleDateString("id-ID", {
                              day: "numeric",
                              month: "short",
                              year: "numeric",
                            })}
                        </div>
                        ${actionButtons}
                    </div>
                </div>
            `;
      })
      .join("");

    // Update filter tabs
    document.querySelectorAll(".filter-tab").forEach((tab) => {
      tab.classList.remove("active");
      if (tab.dataset.filter === filter) {
        tab.classList.add("active");
      }
    });
  }

  window.renderSubmissions = renderSubmissions;

  // --- TEMPLATE KONTEN UNTUK SETIAP HALAMAN ---
  const pageTemplates = {
    dashboard: `
            <div class="metrics-row">
                <div class="metric-box total-users-box">
                    <i class="fas fa-users"></i>
                    <span class="metric-value">0</span>
                    <span class="metric-label">Total Users</span>
                </div>
                <div class="metric-box total-animals-box">
                    <i class="fas fa-paw"></i>
                    <span class="metric-value">0</span>
                    <span class="metric-label">Total Animals</span>
                </div>
                <div class="metric-box pending-reports-box">
                    <i class="fas fa-file-alt"></i>
                    <span class="metric-value">0</span>
                    <span class="metric-label">Pending Reports</span>
                </div>
            </div>
            
            <div class="content-row">
                <div class="recent-activity-box">
                    <h3>Recent Activity</h3>
                    <div class="placeholder-activity-text">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2em; margin-bottom: 10px; color: #aaa;"></i>
                        <p>Loading recent activity...</p>
                    </div>
                </div>

                <div class="insights-chart-box">
                    <h3>Insights Chart</h3>
                    <div class="chart-container" style="position: relative; height: 200px; margin: 20px 0;">
                        <canvas id="insights-chart"></canvas>
                    </div>
                    <div class="insights-chart-legend">
                        <div class="legend-item"><span class="legend-swatch" style="background: var(--metric-brown-dark);"></span> New Users</div>
                        <div class="legend-item"><span class="legend-swatch" style="background: var(--color-secondary-brown);"></span> New Animals</div>
                        <div class="legend-item"><span class="legend-swatch" style="background: var(--color-accent-green);"></span> System Reports</div>
                    </div>
                </div>
            </div>
        `,
    "manage-users": `
            <h3>Manage Users</h3>
            <div style="margin-bottom: 12px; color: #444;">Showing ${
              mockUsers.length
            } of ${mockUsers.length} users</div>
            
            <div class="content-body">
                <div class="user-table-section">
                    <div class="manage-users-panel">
                        <div class="table-chips" role="list">
                            <span class="chip">Name</span>
                            <span class="chip">Email</span>
                            <span class="chip">Status</span>
                            <span class="chip">Action</span>
                        </div>
                        
                        <div class="manage-users-inner">
                            <div class="table-box">
                                <div class="table-container" style="padding: 0; box-shadow: none; background: transparent;">
                                    <table class="data-table user-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th style="text-align:center;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="users-table-body">
                                            <tr class="empty-row">
                          <td colspan="4" style="text-align: center; color: #888; padding: 40px; background: transparent; border-radius: 6px;">
                                                    <i class="fas fa-users" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                                                    Data user akan dimuat di sini setelah koneksi database.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pagination-pill">
                            <div class="pill">Showing 1-10 of ${
                              mockUsers.length
                            } users &nbsp;&nbsp; Prev 1 2 3 ...</div>
                        </div>
                    </div>
                </div>

                <div class="summary-panel">
                    <div class="small-summary-card first">
                        <div class="small-number">${mockUsers.length}</div>
                        <div class="small-label">Total Users</div>
                    </div>
                    <div class="small-summary-card">
                        <div class="small-number">${
                          mockUsers.filter((u) => u.role === "owner").length
                        }</div>
                        <div class="small-label">Total Owners</div>
                    </div>
                    <div class="small-summary-card">
                        <div class="small-number">${
                          mockUsers.filter((u) => u.role === "adopter").length
                        }</div>
                        <div class="small-label">Total Adopters</div>
                    </div>
                </div>
            </div>
        `,
    "manage-animals": `
            <h3>Manage Animals</h3>
            <div style="margin-bottom: 12px; color: #444;" id="animals-count">Loading animals...</div>
            
            <div class="content-body">
                <div class="user-table-section" style="flex: 1;">
                    <div class="animals-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px;" id="animals-grid">
                        <div style="grid-column: 1 / -1; text-align: center; color: #888; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                            Loading animals...
                        </div>
                    </div>
                </div>

                <div class="summary-panel">
                    <div class="small-summary-card first">
                        <div class="small-number" id="summary-total">0</div>
                        <div class="small-label">Total Animals</div>
                    </div>
                    <div class="small-summary-card">
                        <div class="small-number" id="summary-dogs">0</div>
                        <div class="small-label">Total Dogs</div>
                    </div>
                    <div class="small-summary-card">
                        <div class="small-number" id="summary-cats">0</div>
                        <div class="small-label">Total Cats</div>
                    </div>
                    <div class="small-summary-card">
                        <div class="small-number" id="summary-rabbits">0</div>
                        <div class="small-label">Total Rabbits</div>
                    </div>
                </div>
            </div>
        `,
    "system-reports": `
            <div class="metrics-row">
                <div class="metric-box" style="background-color: var(--color-primary-light);">
                    <i class="fas fa-file-alt"></i>
                    <span class="metric-value" id="total-reports-count">${mockReports.length}</span>
                    <span class="metric-label">Total Reports</span>
                </div>
                <div class="metric-box" style="background-color: var(--color-primary-light);">
                    <i class="fas fa-clock"></i>
                    <span class="metric-value" id="pending-reports-count">${
                      mockReports.filter((r) => (r.status || '').toLowerCase() === 'pending').length
                    }</span>
                    <span class="metric-label">Pending Reports</span>
                </div>
                <div class="metric-box" style="background-color: var(--color-primary-light);">
                    <i class="fas fa-check-circle"></i>
                    <span class="metric-value" id="done-reports-count">${
                      mockReports.filter((r) => (r.status || '').toLowerCase() === 'done').length
                    }</span>
                    <span class="metric-label">Done Reports</span>
                </div>
            </div>

            <div class="reports-content-row">
                <div class="calendar-notes-box">
                    <div class="table-container">
                        <div class="calendar-header">
                            <i class="fas fa-chevron-left" id="prev-month" style="cursor: pointer;"></i> 
                            <span id="current-month-year" style="font-weight: bold;"></span>
                            <i class="fas fa-chevron-right" id="next-month" style="cursor: pointer;"></i>
                        </div>
                        <div class="calendar-grid" id="calendar-grid"></div>
                    </div>
                    <div class="table-container">
                        <div style="margin-bottom: 10px;">
                            <h4 style="color: var(--color-primary-dark); font-size: 1em; margin: 0;">Notes</h4>
                        </div>
                        <div style="position: relative;">
                            <textarea id="notes-textarea" style="width: 100%; min-height: 120px; padding: 10px 10px 45px 45px; border: 1px solid var(--color-border); border-radius: 8px; resize: vertical;" placeholder="Tulis catatan penting tentang laporan di sini..."></textarea>
                            <button id="save-notes-btn" style="position: absolute; bottom: 10px; left: 10px; background: var(--color-primary-dark); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10;" onmouseover="this.style.background='var(--color-primary-light)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='var(--color-primary-dark)'; this.style.transform='scale(1)';" title="Save Notes">
                                <i class="fas fa-paper-plane" style="font-size: 0.9em;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="reports-list-box">
                    <div class="reports-list-header">
                        <h3 id="reports-header-title">Reports on ${currentDate.toLocaleDateString(
                          "en-US",
                          { day: "numeric", month: "long", year: "numeric" }
                        )}</h3>
                        <button class="btn btn-submit" id="create-report-btn">+ Create Report</button> 
                    </div>
                    <div id="reports-table-container">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Report Type</th>
                                    <th>Date Range</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th> 
                                </tr>
                            </thead>
                            <tbody id="reports-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `,
    submissions: `
            <div class="submissions-header">
                <h3>Adoption Submissions</h3>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all" onclick="renderSubmissions('all')">
                        <i class="fas fa-list"></i> All
                    </button>
                    <button class="filter-tab" data-filter="pending" onclick="renderSubmissions('pending')">
                        <i class="fas fa-clock"></i> Pending
                    </button>
                    <button class="filter-tab" data-filter="approved" onclick="renderSubmissions('approved')">
                        <i class="fas fa-check-circle"></i> Approved
                    </button>
                    <button class="filter-tab" data-filter="rejected" onclick="renderSubmissions('rejected')">
                        <i class="fas fa-times-circle"></i> Rejected
                    </button>
                </div>
            </div>
            <div class="submissions-grid" id="submissions-grid"></div>
        `,
    "rehome-submissions": `
            <div class="submissions-header">
                <h3>Rehome Submissions</h3>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all" onclick="renderRehomeSubmissions('all')">
                        <i class="fas fa-list"></i> All
                    </button>
                    <button class="filter-tab" data-filter="submitted" onclick="renderRehomeSubmissions('submitted')">
                        <i class="fas fa-hourglass-start"></i> Pending
                    </button>
                    <button class="filter-tab" data-filter="approved" onclick="renderRehomeSubmissions('approved')">
                        <i class="fas fa-check-circle"></i> Approved
                    </button>
                    <button class="filter-tab" data-filter="rejected" onclick="renderRehomeSubmissions('rejected')">
                        <i class="fas fa-times-circle"></i> Rejected
                    </button>
                </div>
            </div>
            <div class="submissions-grid" id="rehome-submissions-grid"></div>
        `,
  };

  // --- LOAD DASHBOARD DATA FROM BACKEND ---
  async function loadDashboardData() {
    try {
      const response = await fetch("../admin/dashboard_api.php", {
        credentials: "include",
      });
      
      if (!response.ok) {
        throw new Error("Failed to load dashboard data");
      }
      
      const data = await response.json();
      
      // Update metrics
      const totalUsersEl = document.querySelector(".total-users-box .metric-value");
      const totalAnimalsEl = document.querySelector(".total-animals-box .metric-value");
      const pendingReportsEl = document.querySelector(".pending-reports-box .metric-value");
      
      if (totalUsersEl) totalUsersEl.textContent = data.total_users || 0;
      if (totalAnimalsEl) totalAnimalsEl.textContent = data.total_animals || 0;
      if (pendingReportsEl) pendingReportsEl.textContent = data.pending_reports || 0;
      
      // Update recent activity as table
      const recentActivityBox = document.querySelector(".recent-activity-box");
      if (recentActivityBox && data.recent_activity && data.recent_activity.length > 0) {
        const placeholder = recentActivityBox.querySelector(".placeholder-activity-text");
        if (placeholder) placeholder.remove();
        
        let activityHTML = `
          <table class="activity-table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th>User</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
        `;
        
        data.recent_activity.forEach((activity) => {
          const date = new Date(activity.date);
          const formattedDate = date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
          });
          
          const typeIcon = activity.type === 'adoption' ? 'fa-heart' : 'fa-home';
          const typeLabel = activity.type === 'adoption' ? 'Adoption' : 'Rehome';
          const statusClass = activity.status === 'approved' ? 'status-approved' : 
                             activity.status === 'rejected' ? 'status-rejected' : 'status-pending';
          const pageTarget = activity.type === 'adoption' ? 'submissions' : 'rehome-submissions';
          const statusText = activity.status.charAt(0).toUpperCase() + activity.status.slice(1);
          
          activityHTML += `
            <tr class="activity-row" data-type="${activity.type}" data-id="${activity.id}" data-page="${pageTarget}" style="cursor: pointer;">
              <td>
                <i class="fas ${typeIcon}" style="color: var(--color-primary-dark); margin-right: 8px;"></i>
                ${typeLabel}
              </td>
              <td>${activity.description}</td>
              <td>${activity.user_name}</td>
              <td>${formattedDate}</td>
              <td><span class="${statusClass}">${statusText}</span></td>
            </tr>
          `;
        });
        
        activityHTML += `
            </tbody>
          </table>
        `;
        
        // Wrap table in scrollable container
        const wrappedHTML = `<div class="activity-table-wrapper">${activityHTML}</div>`;
        
        const existingWrapper = recentActivityBox.querySelector(".activity-table-wrapper");
        const existingTable = recentActivityBox.querySelector(".activity-table");
        
        if (existingWrapper) {
          existingWrapper.innerHTML = activityHTML;
        } else if (existingTable) {
          // Replace existing table with wrapped version
          existingTable.outerHTML = wrappedHTML;
        } else {
          const h3 = recentActivityBox.querySelector("h3");
          if (h3) {
            recentActivityBox.innerHTML = '<h3>Recent Activity</h3>' + wrappedHTML;
          } else {
            recentActivityBox.innerHTML = wrappedHTML;
          }
        }
        
        // Add click handlers for table rows
        const activityRows = recentActivityBox.querySelectorAll(".activity-row");
        activityRows.forEach(row => {
          row.addEventListener("click", function() {
            const pageTarget = this.getAttribute("data-page");
            const activityId = this.getAttribute("data-id");
            const activityType = this.getAttribute("data-type");
            
            if (pageTarget) {
              navigateTo(pageTarget);
              
              // Open detail modal after navigation
              setTimeout(() => {
                if (activityType === 'rehome' && activityId && typeof openRehomeDetail === 'function') {
                  openRehomeDetail(parseInt(activityId));
                } else if (activityType === 'adoption' && activityId && typeof showSubmissionDetail === 'function') {
                  showSubmissionDetail(parseInt(activityId));
                }
              }, 500);
            }
          });
        });
      } else if (recentActivityBox && (!data.recent_activity || data.recent_activity.length === 0)) {
        const placeholder = recentActivityBox.querySelector(".placeholder-activity-text");
        if (!placeholder) {
          recentActivityBox.innerHTML = `
            <h3>Recent Activity</h3>
            <div class="placeholder-activity-text">
              <i class="fas fa-chalkboard" style="font-size: 2em; margin-bottom: 10px; color: #aaa;"></i>
              <p>No recent activity found.</p>
            </div>
          `;
        }
      }
      
      // Update insights chart with donut chart
      updateInsightsChart(data);
      
      return data;
    } catch (error) {
      console.error("Error loading dashboard data:", error);
      return null;
    }
  }

  // --- UPDATE INSIGHTS CHART (DONUT CHART) ---
  let insightsChart = null;
  function updateInsightsChart(data) {
    const chartCanvas = document.getElementById("insights-chart");
    if (!chartCanvas) return;
    
    const chartData = data.chart_data || {
      new_users: 0,
      new_animals: 0,
      system_reports: 0
    };
    
    // Destroy existing chart if it exists
    if (insightsChart) {
      insightsChart.destroy();
    }
    
    // Create new donut chart
    const ctx = chartCanvas.getContext('2d');
    insightsChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['New Users', 'New Animals', 'System Reports'],
        datasets: [{
          data: [
            chartData.new_users || 0,
            chartData.new_animals || 0,
            chartData.system_reports || 0
          ],
          backgroundColor: [
            '#a97b5b', // metric-brown-dark
            '#c79a76', // color-secondary-brown
            '#a3c9a8'  // color-accent-green
          ],
          borderWidth: 0,
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
        cutout: '60%',
        animation: {
          animateRotate: true,
          animateScale: true
        }
      }
    });
  }

  // --- FUNGSI NAVIGASI UTAMA ---
  function navigateTo(pageId) {
    pageContent.innerHTML = pageTemplates[pageId];

    navItems.forEach((li) => li.classList.remove("active"));
    const activeItem = document.querySelector(
      `.nav-menu li[data-page="${pageId}"]`
    );
    if (activeItem) {
      activeItem.classList.add("active");
    }

    if (pageId === "dashboard") {
      // Load dashboard data from backend
      loadDashboardData();
    }

    if (pageId === "manage-animals") {
      // Load animals data from backend
      loadAnimalsFromBackend();
    }

    if (pageId === "system-reports") {
      // Load reports from backend first
      loadReportsFromBackend().then(() => {
      const dynamicCreateReportBtn =
        document.getElementById("create-report-btn");
      const calendarGrid = document.getElementById("calendar-grid");
      const prevMonthBtn = document.getElementById("prev-month");
      const nextMonthBtn = document.getElementById("next-month");

      if (prevMonthBtn)
        prevMonthBtn.addEventListener("click", () => changeMonth(-1));
      if (nextMonthBtn)
        nextMonthBtn.addEventListener("click", () => changeMonth(1));
        if (calendarGrid) {
          calendarGrid.addEventListener("click", handleDateClick);
          calendarGrid.addEventListener("mousedown", handleDateClick);
          calendarGrid.addEventListener("mouseup", handleDateClick);
          calendarGrid.addEventListener("mouseenter", (e) => {
            if (e.target.closest(".date-cell")) {
              handleDateClick(e);
            }
          });
        }

      renderCalendar(currentDate);
      updateReportsView(
        currentDate.getDate(),
        currentDate.getMonth(),
        currentDate.getFullYear()
      );

      if (dynamicCreateReportBtn) {
          // Remove existing listeners to prevent duplicates
          const newBtn = dynamicCreateReportBtn.cloneNode(true);
          dynamicCreateReportBtn.parentNode.replaceChild(newBtn, dynamicCreateReportBtn);
          
          newBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            console.log("Create Report button clicked (direct handler)");
            
            const modal = document.getElementById("create-report-modal");
            if (!modal) {
              console.error("Create report modal not found in DOM");
              return;
            }
            
            console.log("Modal found, opening...");
            modal.style.display = "flex";
            modal.style.zIndex = "10000";
            modal.classList.add("active");
            
            // Get selected date range or use current date
            let startDate, endDate;
            if (selectedDateRange.start && selectedDateRange.end) {
              const start = selectedDateRange.start < selectedDateRange.end 
                ? selectedDateRange.start 
                : selectedDateRange.end;
              const end = selectedDateRange.start < selectedDateRange.end 
                ? selectedDateRange.end 
                : selectedDateRange.start;
              
              startDate = formatDateForInput(start);
              endDate = formatDateForInput(end);
            } else {
              // Use selected day from calendar if available
              if (window.selectedDay !== null && window.selectedDay !== undefined) {
                // Use selected day from calendar
                const selectedDate = new Date(currentDate);
                selectedDate.setDate(window.selectedDay);
                const formattedDate = `${selectedDate.getFullYear()}-${(
                  selectedDate.getMonth() + 1
                )
                  .toString()
                  .padStart(2, "0")}-${selectedDate
                  .getDate()
                  .toString()
                  .padStart(2, "0")}`;
                startDate = formattedDate;
                endDate = formattedDate;
              } else {
                // Use current date as fallback
          const formattedDate = `${currentDate.getFullYear()}-${(
            currentDate.getMonth() + 1
          )
            .toString()
            .padStart(2, "0")}-${currentDate
            .getDate()
            .toString()
            .padStart(2, "0")}`;
                startDate = formattedDate;
                endDate = formattedDate;
              }
            }
            
            const startDateInput = document.getElementById("report-start-date");
            const endDateInput = document.getElementById("report-end-date");
            if (startDateInput) startDateInput.value = startDate;
            if (endDateInput) endDateInput.value = endDate;
            
            // Reset form
            const form = document.getElementById("report-form");
            if (form) {
              const descTextarea = document.getElementById("report-description");
              if (descTextarea) descTextarea.value = "";
              const reportType = document.getElementById("report-type");
              if (reportType) reportType.value = "Daily";
            }
          });
        }
        
        // Form submit handler is already handled by event delegation at document level
        // No need to add another listener here to prevent duplicates
        
        // Add event listener for Save Notes button
        const saveNotesBtn = document.getElementById("save-notes-btn");
        if (saveNotesBtn) {
          saveNotesBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            e.stopPropagation();
            await saveNotesReport();
        });
      }

      const reportsBody = document.getElementById("reports-body");
      if (reportsBody) {
        reportsBody.addEventListener("click", (e) => {
          const row = e.target.closest("tr");
          const actionButton = e.target.closest(".report-action-btn");

          if (row && !actionButton) {
            const reportId = parseInt(row.getAttribute("data-report-id"));
            if (reportId) {
              showReportDetail(reportId);
            }
          }
        });
      }
      });
    }

    if (pageId === "submissions") {
      // Load submissions from backend before rendering
      const grid = document.getElementById("submissions-grid");
      if (grid) {
        grid.innerHTML =
          '<div style="grid-column:1 / -1; text-align:center; color:#777; padding:16px;"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</div>';
      }
      loadSubmissionsFromBackend()
        .then(() => {
          if (!mockSubmissions || mockSubmissions.length === 0) {
            if (grid) {
              grid.innerHTML =
                '<div class="empty-submissions" style="grid-column: 1 / -1; text-align:center; color:#888; padding:16px;"><i class="fas fa-clipboard-list"></i> No submissions found. If you expect data, verify the API and database.</div>';
            }
          }
          renderSubmissions("all");
        })
        .catch((err) => {
          console.warn("Failed to load submissions:", err);
          // Fallback to current mock state if API fails
          renderSubmissions("all");
        });
    }

    if (pageId === "rehome-submissions") {
      // Load rehome submissions from backend before rendering
      const grid = document.getElementById("rehome-submissions-grid");
      
      if (grid) {
        grid.innerHTML =
          '<div style="grid-column:1 / -1; text-align:center; color:#777; padding:16px;"><i class="fas fa-spinner fa-spin"></i> Loading rehome submissions...</div>';
      }
      loadRehomeSubmissionsFromBackend()
        .then(() => {
          if (!mockRehomeSubmissions || mockRehomeSubmissions.length === 0) {
            if (grid) {
              grid.innerHTML =
                '<div class="empty-submissions" style="grid-column: 1 / -1; text-align:center; color:#888; padding:16px;"><i class="fas fa-clipboard-list"></i> No rehome submissions found. If you expect data, verify the API and database.</div>';
            }
          }
          renderRehomeSubmissions("all");
        })
        .catch((err) => {
          console.warn("Failed to load rehome submissions:", err);
          // Fallback to current mock state if API fails
          renderRehomeSubmissions("all");
        });
    }

    if (pageId === "manage-users") {
      const tbody = document.getElementById("users-table-body");
      if (tbody) {
        tbody.innerHTML = `
          <tr class="empty-row">
            <td colspan="5" style="text-align:center; color:#888; padding:20px;">
              <i class="fas fa-spinner fa-spin"></i> Loading users...
            </td>
          </tr>`;
      }
      loadUsersFromBackend()
        .then(() => {
          renderManageUsers();
        })
        .catch((err) => {
          console.warn("Failed to load users:", err);
          renderManageUsers(); // Render any existing mockUsers (possibly empty)
        });
    }
  }

  // --- LOGIKA PERUBAHAN BULAN ---
  function changeMonth(step) {
    const currentDay = currentDate.getDate();
    const currentMonth = currentDate.getMonth();
    const newMonth = currentMonth + step;

    let newDate = new Date(currentDate.getFullYear(), newMonth, currentDay);

    if (newDate.getMonth() !== ((newMonth % 12) + 12) % 12) {
      newDate = new Date(currentDate.getFullYear(), newMonth + 1, 0);
    }

    currentDate = newDate;
    window.currentDate = currentDate; // Update global reference

    renderCalendar(currentDate);
    updateReportsView(
      currentDate.getDate(),
      currentDate.getMonth(),
      currentDate.getFullYear()
    );
  }

  // --- LOGIKA KLIK TANGGAL DENGAN DRAG SELECTION ---
  function handleDateClick(e) {
    const targetCell = e.target.closest(".date-cell");
    if (!targetCell || targetCell.classList.contains("empty-cell")) return;

    const selectedDayNum = parseInt(targetCell.textContent);
    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    const clickedDate = new Date(currentYear, currentMonth, selectedDayNum);
    
    // Store selected day globally
    window.selectedDay = selectedDayNum;
    selectedDay = selectedDayNum;

    // Handle mouse down - start selection
    if (e.type === "mousedown") {
      isDragging = true;
      selectedDateRange.start = clickedDate;
      selectedDateRange.end = clickedDate;
      window.selectedDateRange = selectedDateRange; // Update global reference
      
      // Clear previous selections
      document.querySelectorAll(".date-cell").forEach((cell) => {
        cell.classList.remove("selected-day", "range-start", "range-end", "range-middle");
      });
      
      targetCell.classList.add("selected-day", "range-start", "range-end");
      e.preventDefault();
      return;
    }

    // Handle mouse enter during drag
    if (e.type === "mouseenter" && isDragging && selectedDateRange.start) {
      selectedDateRange.end = clickedDate;
      window.selectedDateRange = selectedDateRange; // Update global reference
      updateDateRangeSelection();
      return;
    }

    // Handle mouse up - end selection
    if (e.type === "mouseup" && isDragging) {
      isDragging = false;
      selectedDateRange.end = clickedDate;
      
      const start = selectedDateRange.start < selectedDateRange.end 
        ? selectedDateRange.start 
        : selectedDateRange.end;
      const end = selectedDateRange.start < selectedDateRange.end 
        ? selectedDateRange.end 
        : selectedDateRange.start;
      
      updateDateRangeSelection();
      
      // Update form dates if modal is open
      const modal = document.getElementById("create-report-modal");
      if (modal && modal.style.display === "flex") {
        document.getElementById("report-start-date").value = 
          formatDateForInput(start);
        document.getElementById("report-end-date").value = 
          formatDateForInput(end);
      }
      
      // Update current date and view with range
      currentDate = new Date(clickedDate);
      window.currentDate = currentDate; // Update global reference
      window.selectedDateRange = selectedDateRange; // Update global reference
      
      // Update view with range
      if (start.getTime() !== end.getTime()) {
        updateReportsViewWithRange(start, end);
      } else {
        updateReportsView(selectedDay, currentMonth, currentYear);
      }
      
      // Load notes for selected date (single click)
      const notesTextarea = document.getElementById("notes-textarea");
      if (notesTextarea) {
        const dateString = `${clickedDate.getFullYear()}-${(
          clickedDate.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${clickedDate
          .getDate()
          .toString()
          .padStart(2, "0")}`;
        
        // Find notes report for this date
        const notesReport = mockReports.find((report) => {
          if (report.report_type === "Notes" && report.start_date && report.end_date) {
            const reportStart = new Date(report.start_date + 'T00:00:00');
            const reportEnd = new Date(report.end_date + 'T23:59:59');
            const selectedDate = new Date(dateString + 'T00:00:00');
            return selectedDate >= reportStart && selectedDate <= reportEnd;
          }
          return false;
        });
        
        if (notesReport) {
          notesTextarea.value = notesReport.description || "";
        } else {
          notesTextarea.value = "";
        }
      }
      
      return;
    }
    
    // Single click - select single date
    if (e.type === "click" && !isDragging) {
      document
        .querySelectorAll(".date-cell")
        .forEach((cell) => cell.classList.remove("selected-day", "range-start", "range-end", "range-middle"));

      currentDate = clickedDate;
      window.currentDate = currentDate; // Update global reference
      targetCell.classList.add("selected-day");
      selectedDateRange.start = clickedDate;
      selectedDateRange.end = clickedDate;
      window.selectedDateRange = selectedDateRange; // Update global reference

      updateReportsView(selectedDay, currentMonth, currentYear);
      
      // Load notes for selected date
      const notesTextarea = document.getElementById("notes-textarea");
      if (notesTextarea) {
        const dateString = `${clickedDate.getFullYear()}-${(
          clickedDate.getMonth() + 1
        )
          .toString()
          .padStart(2, "0")}-${clickedDate
          .getDate()
          .toString()
          .padStart(2, "0")}`;
        
        // Find notes report for this date
        const notesReport = mockReports.find((report) => {
          if (report.report_type === "Notes" && report.start_date && report.end_date) {
            const reportStart = new Date(report.start_date + 'T00:00:00');
            const reportEnd = new Date(report.end_date + 'T23:59:59');
            const selectedDate = new Date(dateString + 'T00:00:00');
            return selectedDate >= reportStart && selectedDate <= reportEnd;
          }
          return false;
        });
        
        if (notesReport) {
          notesTextarea.value = notesReport.description || "";
        } else {
          notesTextarea.value = "";
        }
      }
      
      // Update header to show single date
      const headerTitle = document.getElementById("reports-header-title");
      if (headerTitle) {
        const month = new Date(currentYear, currentMonth).toLocaleString("en-US", {
          month: "long",
        });
        headerTitle.textContent = `Reports on ${selectedDay} ${month} ${currentYear}`;
      }
    }
  }

  function formatDateForInput(date) {
    if (!date || !(date instanceof Date)) return "";
    return `${date.getFullYear()}-${(date.getMonth() + 1)
      .toString()
      .padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
  }
  
  // Make formatDateForInput accessible globally
  window.formatDateForInput = formatDateForInput;

  function updateDateRangeSelection() {
    if (!selectedDateRange.start || !selectedDateRange.end) return;

    const start = selectedDateRange.start < selectedDateRange.end 
      ? selectedDateRange.start 
      : selectedDateRange.end;
    const end = selectedDateRange.start < selectedDateRange.end 
      ? selectedDateRange.end 
      : selectedDateRange.start;

    document.querySelectorAll(".date-cell").forEach((cell) => {
      cell.classList.remove("selected-day", "range-start", "range-end", "range-middle");
      
      const cellDay = parseInt(cell.textContent);
      if (isNaN(cellDay)) return;
      
      const cellDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), cellDay);
      
      if (cellDate.getTime() === start.getTime()) {
        cell.classList.add("selected-day", "range-start");
      } else if (cellDate.getTime() === end.getTime()) {
        cell.classList.add("selected-day", "range-end");
      } else if (cellDate > start && cellDate < end) {
        cell.classList.add("selected-day", "range-middle");
      }
    });
    
    // Update header to show range
    if (start.getTime() !== end.getTime()) {
      const headerTitle = document.getElementById("reports-header-title");
      if (headerTitle) {
        const startDay = start.getDate();
        const endDay = end.getDate();
        const month = start.toLocaleString("en-US", { month: "long" });
        const year = start.getFullYear();
        
        // Only update if both dates are in the same month
        if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
          headerTitle.textContent = `Reports on ${startDay}-${endDay} ${month} ${year}`;
          
          // Also update the reports view
          updateReportsViewWithRange(start, end);
        }
      }
    }
  }

  // --- LOGIKA UPDATE TAMPILAN LAPORAN DENGAN RANGE ---
  function updateReportsViewWithRange(startDate, endDate) {
    const headerTitle = document.getElementById("reports-header-title");
    const reportsBody = document.getElementById("reports-body");

    if (!headerTitle || !reportsBody) return;

    const startDay = startDate.getDate();
    const endDay = endDate.getDate();
    const month = startDate.toLocaleString("en-US", { month: "long" });
    const year = startDate.getFullYear();
    
    if (startDay === endDay) {
      headerTitle.textContent = `Reports on ${startDay} ${month} ${year}`;
    } else {
      headerTitle.textContent = `Reports on ${startDay}-${endDay} ${month} ${year}`;
    }

    // Filter reports for the date range
    const reportsForDate = mockReports.filter((report) => {
      const reportStartDate = report.start_date ? new Date(report.start_date + 'T00:00:00') : null;
      const reportEndDate = report.end_date ? new Date(report.end_date + 'T23:59:59') : null;
      const reportDate = report.created_at ? new Date(report.created_at) : 
                        (report.date ? new Date(report.date + 'T00:00:00') : null);
      
      // Normalize dates to start of day for comparison
      const start = new Date(startDate);
      start.setHours(0, 0, 0, 0);
      const end = new Date(endDate);
      end.setHours(23, 59, 59, 999);
      
      if (reportStartDate && reportEndDate) {
        // Check if report date range overlaps with selected range
        const reportStart = new Date(reportStartDate);
        reportStart.setHours(0, 0, 0, 0);
        const reportEnd = new Date(reportEndDate);
        reportEnd.setHours(23, 59, 59, 999);
        return (reportStart <= end && reportEnd >= start);
      } else if (reportDate) {
        // Check if report date is within selected range
        const report = new Date(reportDate);
        report.setHours(0, 0, 0, 0);
        return (report >= start && report <= end);
      }
      return false;
    });

    renderReportsTable(reportsForDate, reportsBody);
    
    // Load notes for selected date range (use start date)
    const notesTextarea = document.getElementById("notes-textarea");
    if (notesTextarea) {
      const notesReport = reportsForDate.find((report) => report.report_type === "Notes");
      if (notesReport) {
        notesTextarea.value = notesReport.description || "";
      } else {
        notesTextarea.value = "";
      }
    }
    
    // Update metrics
    updateReportsMetrics();
  }
  
  // --- UPDATE REPORTS METRICS ---
  function updateReportsMetrics() {
    if (document.querySelector(".metrics-row")) {
      const totalReportsEl = document.getElementById("total-reports-count");
      const pendingReportsEl = document.getElementById("pending-reports-count");
      const doneReportsEl = document.getElementById("done-reports-count");
      
      if (totalReportsEl) totalReportsEl.textContent = mockReports.length;
      if (pendingReportsEl) {
        pendingReportsEl.textContent = mockReports.filter((r) => (r.status || '').toLowerCase() === 'pending').length;
      }
      if (doneReportsEl) {
        doneReportsEl.textContent = mockReports.filter((r) => (r.status || '').toLowerCase() === 'done').length;
      }
    }
  }
  
  // --- SAVE NOTES REPORT ---
  window.saveNotesReport = async function() {
    const notesTextarea = document.getElementById("notes-textarea");
    if (!notesTextarea) {
      alert("Notes textarea not found");
      return;
    }
    
    const notesContent = notesTextarea.value.trim();
    // Allow empty notes to be saved (user might want to clear notes)
    
    // Get selected date
    const selectedDayNum = window.selectedDay || selectedDay;
    const currentDateForNotes = window.currentDate || currentDate || new Date();
    const selectedDate = new Date(
      currentDateForNotes.getFullYear(),
      currentDateForNotes.getMonth(),
      selectedDayNum || currentDateForNotes.getDate()
    );
    
    const dateString = `${selectedDate.getFullYear()}-${(
      selectedDate.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")}-${selectedDate
      .getDate()
      .toString()
      .padStart(2, "0")}`;
    
    try {
      const response = await fetch("../admin/reports_api.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
          report_type: "Notes",
          start_date: dateString,
          end_date: dateString,
          description: notesContent,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        // Clear textarea
        notesTextarea.value = "";
        
        // Reload reports from backend
        await loadReportsFromBackend();
        
        // Update metrics
        updateReportsMetrics();
        
        // Update view
        const reportsBody = document.getElementById("reports-body");
        if (reportsBody) {
          const dateRange = window.selectedDateRange || selectedDateRange;
          if (dateRange && dateRange.start && dateRange.end && 
              dateRange.start.getTime() !== dateRange.end.getTime()) {
            const start = dateRange.start < dateRange.end 
              ? dateRange.start 
              : dateRange.end;
            const end = dateRange.start < dateRange.end 
              ? dateRange.end 
              : dateRange.start;
            updateReportsViewWithRange(start, end);
          } else {
      updateReportsView(
              selectedDate.getDate(),
              selectedDate.getMonth(),
              selectedDate.getFullYear()
            );
          }
        }
        
        // Re-render calendar to show updated data
        if (window.currentDate) {
          renderCalendar(window.currentDate);
        } else if (currentDate) {
          renderCalendar(currentDate);
        }
        
        alert("Notes saved successfully!");
      } else {
        alert("Error: " + (data.message || "Failed to save notes"));
      }
    } catch (error) {
      console.error("Error saving notes:", error);
      alert("Error saving notes. Please try again.");
    }
  };
  
  // --- TOGGLE REPORT STATUS ---
  window.toggleReportStatus = async function(reportId) {
    const report = mockReports.find((r) => r.id === reportId);
    if (!report) {
      alert("Report not found");
      return;
    }
    
    const currentStatus = (report.status || '').toLowerCase();
    const newStatus = currentStatus === 'pending' ? 'done' : 'pending';
    
    try {
      const response = await fetch("../admin/reports_api.php", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
          id: reportId,
          status: newStatus,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        // Update local data
        report.status = newStatus;
        
        // Reload reports from backend to ensure sync
        await loadReportsFromBackend();
        
        // Update view
        const reportsBody = document.getElementById("reports-body");
        if (reportsBody) {
          // Get current date selection
          const dateRange = window.selectedDateRange || selectedDateRange;
          if (dateRange && dateRange.start && dateRange.end && 
              dateRange.start.getTime() !== dateRange.end.getTime()) {
            const start = dateRange.start < dateRange.end 
              ? dateRange.start 
              : dateRange.end;
            const end = dateRange.start < dateRange.end 
              ? dateRange.end 
              : dateRange.start;
            updateReportsViewWithRange(start, end);
          } else {
            const currentDateForView = window.currentDate || currentDate || new Date();
            updateReportsView(
              currentDateForView.getDate(),
              currentDateForView.getMonth(),
              currentDateForView.getFullYear()
            );
          }
        }
        
        // Update metrics
        updateReportsMetrics();
      } else {
        alert("Error: " + (data.message || "Failed to update report status"));
      }
    } catch (error) {
      console.error("Error updating report status:", error);
      alert("Error updating report status. Please try again.");
    }
  };

  // --- LOGIKA UPDATE TAMPILAN LAPORAN ---
  function updateReportsView(day, monthIndex, year) {
    const headerTitle = document.getElementById("reports-header-title");
    const reportsBody = document.getElementById("reports-body");

    if (!headerTitle || !reportsBody) return;

    // Check if there's a date range selected
    if (selectedDateRange.start && selectedDateRange.end && 
        selectedDateRange.start.getTime() !== selectedDateRange.end.getTime()) {
      const start = selectedDateRange.start < selectedDateRange.end 
        ? selectedDateRange.start 
        : selectedDateRange.end;
      const end = selectedDateRange.start < selectedDateRange.end 
        ? selectedDateRange.end 
        : selectedDateRange.start;
      
      // Only show range if both dates are in the same month
      if (start.getMonth() === monthIndex && end.getMonth() === monthIndex && 
          start.getFullYear() === year && end.getFullYear() === year) {
        updateReportsViewWithRange(start, end);
        return;
      }
    }

    const month = new Date(year, monthIndex).toLocaleString("en-US", {
      month: "long",
    });
    headerTitle.textContent = `Reports on ${day} ${month} ${year}`;

    const targetDateString = `${year}-${(monthIndex + 1)
      .toString()
      .padStart(2, "0")}-${day.toString().padStart(2, "0")}`;

    // Filter reports for the selected date
    // Use start_date and end_date from report, not created_at
    const reportsForDate = mockReports.filter((report) => {
      // Check if report's date range includes the selected date
      if (report.start_date && report.end_date) {
        const reportStart = new Date(report.start_date + 'T00:00:00');
        const reportEnd = new Date(report.end_date + 'T23:59:59');
        const selectedDate = new Date(targetDateString + 'T00:00:00');
        
        return selectedDate >= reportStart && selectedDate <= reportEnd;
      } else if (report.created_at || report.date) {
        // Fallback to created_at if start_date/end_date not available
        const reportDate = new Date(report.created_at || report.date);
        const reportDateString = `${reportDate.getFullYear()}-${(reportDate.getMonth() + 1)
          .toString()
          .padStart(2, "0")}-${reportDate.getDate().toString().padStart(2, "0")}`;
        return reportDateString === targetDateString;
      }
      return false;
    });

    renderReportsTable(reportsForDate, reportsBody);
    
    // Load notes for selected date
    const notesTextarea = document.getElementById("notes-textarea");
    if (notesTextarea) {
      const notesReport = reportsForDate.find((report) => report.report_type === "Notes");
      if (notesReport) {
        notesTextarea.value = notesReport.description || "";
      } else {
        notesTextarea.value = "";
      }
    }
    
    // Update metrics
    updateReportsMetrics();
  }

  // --- FUNGSI RENDER TABLE REPORTS ---
  function renderReportsTable(reportsForDate, reportsBody) {
    if (!reportsBody) return;

    if (reportsForDate.length > 0) {
      reportsBody.innerHTML = reportsForDate
        .map((report) => {
          const shortDesc = (report.description || report.desc || "").substring(0, 50) +
            ((report.description || report.desc || "").length > 50 ? "..." : "");

          let statusClass = "";
          const status = (report.status || "").toLowerCase();
          if (status === "done") {
            statusClass = "status-done";
          } else if (status === "pending") {
            statusClass = "status-pending";
          } else if (status === "failed") {
            statusClass = "status-failed";
          }

          const dateRange = report.start_date && report.end_date
            ? `${report.start_date} to ${report.end_date}`
            : report.range || "-";

          return `
                <tr data-report-id="${report.id}" style="cursor: pointer;">
                    <td style="text-align: center;">${report.id
                      .toString()
                      .padStart(3, "0")}</td>
                    <td>${report.report_type || report.type || "Daily"}</td>
                    <td>${dateRange}</td>
                    <td>${shortDesc}</td>
                    <td style="text-align: center;">
                        <span class="${statusClass}" style="cursor: pointer;" onclick="event.stopPropagation(); toggleReportStatus(${report.id})" title="Click to toggle status">${
            (report.status || "Pending").charAt(0).toUpperCase() + (report.status || "Pending").slice(1).toLowerCase()
          }</span>
                    </td>
                    <td style="text-align: center;">
                        <i class="fas fa-edit report-action-btn" onclick="event.stopPropagation(); editReport(${
                          report.id
                        })" title="Edit Report" style="cursor: pointer; color: var(--color-primary-dark);"></i>
                        <i class="fas fa-trash-alt report-action-btn" onclick="event.stopPropagation(); deleteReport(${
                          report.id
                        })" title="Delete Report" style="cursor: pointer; color: var(--color-danger); margin-left: 10px;"></i>
                    </td>
                </tr>
            `;
        })
        .join("");
    } else {
      reportsBody.innerHTML = `
                <tr><td colspan="6" style="text-align: center; color: #888; padding: 20px;">
                    <i class="fas fa-cat" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                    No reports found on this date. Create a new report using the button above.
                </td></tr>
            `;
    }
  }

  // --- LOGIKA KALENDER UTAMA ---
  function renderCalendar(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    const activeDay = date.getDate();

    const monthName = new Date(year, month).toLocaleString("en-US", {
      month: "long",
    });
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const calendarGrid = document.getElementById("calendar-grid");
    const currentMonthYear = document.getElementById("current-month-year");

    if (!calendarGrid || !currentMonthYear) return;

    currentMonthYear.textContent = `${monthName} ${year}`;

    const dayHeaders = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
    let html = '<table class="calendar-table"><thead><tr>';
    dayHeaders.forEach((day) => {
      html += `<th>${day}</th>`;
    });
    html += "</tr></thead><tbody><tr>";

    let dayOfMonth = 1;
    const startOffset = new Date(year, month, 1).getDay();

    for (let i = 0; i < startOffset; i++) {
      html += `<td class="empty-cell"></td>`;
    }

    const datesWithReports = mockReports
      .filter((r) => {
        const reportDate = r.date || (r.created_at ? r.created_at.split(' ')[0] : null);
        return reportDate && reportDate.startsWith(`${year}-${(month + 1).toString().padStart(2, "0")}`);
      })
      .map((r) => {
        const reportDate = r.date || (r.created_at ? r.created_at.split(' ')[0] : null);
        return reportDate ? parseInt(reportDate.substring(8, 10)) : null;
      })
      .filter(d => d !== null);

    for (let i = startOffset; dayOfMonth <= daysInMonth; i++) {
      if (i % 7 === 0 && dayOfMonth > 1) {
        html += "</tr><tr>";
      }

      const isSelectedDay =
        dayOfMonth === activeDay && date.getMonth() === month;
      const hasReport = datesWithReports.includes(dayOfMonth);

      let className = "date-cell";
      if (isSelectedDay) {
        className += " selected-day";
      }
      if (hasReport) {
        className += " has-report";
      }

      html += `<td class="${className}">${dayOfMonth}</td>`;
      dayOfMonth++;
    }

    while ((dayOfMonth + startOffset - 1) % 7 !== 0) {
      html += `<td class="empty-cell"></td>`;
      dayOfMonth++;
    }

    html += "</tr></tbody></table>";
    calendarGrid.innerHTML = html;
  }

  // --- LISTENER SIDEBAR ---
  navItems.forEach((item) => {
    item.addEventListener("click", (e) => {
      const page = e.currentTarget.getAttribute("data-page");
      if (page && page !== "logout") {
        e.preventDefault();
        navigateTo(page);
      }
    });
  });

  // --- INISIALISASI AWAL DASHBOARD ---
  navigateTo("dashboard");
  // Load dashboard data immediately
  loadDashboardData();
}

// =========================================================
// --- AUTO START DASHBOARD ---
// =========================================================
document.addEventListener("DOMContentLoaded", () => {
  setupModalListeners();
  handleFormSubmit("login-tab", "login.php");
  handleFormSubmit("register-tab", "register.php");
  checkUserLogin();
});

// =========================================================
// --- BACKEND INTEGRATION: LOAD REPORTS ---
// =========================================================
async function loadReportsFromBackend() {
  try {
    const resp = await fetch("../admin/reports_api.php", {
      credentials: "include",
    });
    if (!resp.ok) throw new Error("Failed to load reports");
    const json = await resp.json();
    if (json && Array.isArray(json.data)) {
      mockReports = json.data.map((r) => ({
        id: r.id,
        report_type: r.report_type,
        type: r.report_type,
        start_date: r.start_date,
        end_date: r.end_date,
        range: `${r.start_date} to ${r.end_date}`,
        description: r.description,
        desc: r.description,
        status: r.status,
        created_at: r.created_at,
        date: r.created_at ? r.created_at.split(' ')[0] : r.start_date,
        created_by_name: r.created_by_name,
      }));
      console.log("Loaded reports:", mockReports.length);
      
      // Update metrics after loading reports (check if function exists)
      if (typeof updateReportsMetrics === 'function') {
        updateReportsMetrics();
      }
    }
  } catch (e) {
    console.warn("Reports API error:", e);
    // Leave mockReports as-is
  }
}

// =========================================================
// --- BACKEND INTEGRATION: LOAD SUBMISSIONS ---
// =========================================================
async function loadSubmissionsFromBackend() {
  try {
    const resp = await fetch("../admin/submissions_api.php", {
      credentials: "include",
    });
    if (!resp.ok) throw new Error("Failed to load submissions");
    const json = await resp.json();
    if (json && Array.isArray(json.data)) {
      mockSubmissions = json.data.map((it) => ({
        id: it.id,
        adopterName: it.adopterName,
        adopterEmail: it.adopterEmail,
        adopterPhone: it.adopterPhone,
        animalName: it.animalName,
        date: it.date,
        status: it.status,
        reason: it.reason,
        address: it.address,
        city: it.city,
        postcode: it.postcode,
        hasGarden: it.hasGarden,
        living: it.living,
        details: it.details || {},
        home_photos: it.home_photos || [], // Include home photos from API
      }));
      console.log("Loaded submissions:", mockSubmissions.length);
    }
  } catch (e) {
    console.warn("Submissions API error:", e);
    // Leave mockSubmissions as-is
  }
}

// =========================================================
// --- REHOME SUBMISSIONS ---
// =========================================================
let mockRehomeSubmissions = [];

async function loadRehomeSubmissionsFromBackend() {
  try {
    const resp = await fetch("../admin/rehome_submissions_api.php", {
      credentials: "include",
    });
    if (!resp.ok) throw new Error("Failed to load rehome submissions");
    const json = await resp.json();
    if (json && Array.isArray(json.data)) {
      mockRehomeSubmissions = json.data.map((it) => ({
        id: it.id,
        ownerName: it.nama || it.nama_user || 'Unknown',
        ownerEmail: it.email || it.email_user || 'N/A',
        petName: it.pet_name,
        petType: it.pet_type,
        age: it.age_years,
        breed: it.breed,
        gender: it.gender,
        city: it.city,
        postcode: it.postcode,
        date: it.submitted_at,
        status: it.status,
      }));
      console.log("Loaded rehome submissions:", mockRehomeSubmissions.length);
    }
  } catch (e) {
    console.warn("Rehome submissions API error:", e);
    // Leave mockRehomeSubmissions as-is
  }
}

function renderRehomeSubmissions(filter = "all") {
  const grid = document.getElementById("rehome-submissions-grid");
  if (!grid) return;

  let filtered = mockRehomeSubmissions;
  if (filter === "submitted") {
    filtered = mockRehomeSubmissions.filter((s) => s.status === "submitted");
  } else if (filter === "approved") {
    filtered = mockRehomeSubmissions.filter((s) => s.status === "approved");
  } else if (filter === "rejected") {
    filtered = mockRehomeSubmissions.filter((s) => s.status === "rejected");
  }

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="empty-submissions" style="grid-column: 1 / -1;">
        <i class="fas fa-clipboard-list"></i>
        <p style="font-size: 1.1em;">No rehome submissions found for "${filter}" filter.</p>
        <p style="margin-top: 10px; color: #aaa;">Rehome submission forms akan muncul di sini setelah koneksi database.</p>
      </div>
    `;
    return;
  }

  grid.innerHTML = filtered
    .map((submission) => {
      let statusClass =
        submission.status === "approved"
          ? "status-approved"
          : submission.status === "rejected"
          ? "status-rejected"
          : "status-pending";

      return `
        <div class="submission-card rehome-submission-card" data-submission-id="${
          submission.id
        }" style="cursor:pointer;">
          <div class="submission-card-header">
            <div class="submission-id">#${submission.id
              .toString()
              .padStart(3, "0")}</div>
            <span class="${statusClass}">${submission.status.toUpperCase()}</span>
          </div>
          <div class="submission-card-body">
            <div class="submission-info">
              <i class="fas fa-user"></i>
              <span>${submission.ownerName}</span>
            </div>
            <div class="submission-info">
              <i class="fas fa-paw"></i>
              <span>${submission.petName} (${submission.petType})</span>
            </div>
            <div class="submission-info">
              <i class="fas fa-envelope"></i>
              <span>${submission.ownerEmail}</span>
            </div>
            <div class="submission-info">
              <i class="fas fa-map-marker-alt"></i>
              <span>${submission.city}</span>
            </div>
          </div>
          <div class="submission-card-footer">
            <div class="submission-date">
              <i class="fas fa-calendar"></i> ${new Date(
                submission.date
              ).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "short",
                year: "numeric",
              })}
            </div>
          </div>
        </div>
      `;
    })
    .join("");

  // Update filter tabs
  document.querySelectorAll(".filter-tab").forEach((tab) => {
    tab.classList.remove("active");
    if (tab.dataset.filter === filter) {
      tab.classList.add("active");
    }
  });
}

window.renderRehomeSubmissions = renderRehomeSubmissions;

// Open rehome submission detail in modal
window.openRehomeDetail = async function (id) {
  if (!id) {
    console.error("openRehomeDetail: Invalid ID");
    return;
  }
  
  console.log("openRehomeDetail called with ID:", id);
  
  // First, ensure modal exists and is accessible
  const modal = document.getElementById("submission-detail-modal");
  if (!modal) {
    console.error("Submission detail modal not found in DOM");
    alert("Modal not found. Please refresh the page.");
    return;
  }
  
  // Ensure all required elements exist
  const requiredElements = [
    "submission-detail-id",
    "submission-adopter-name",
    "submission-adopter-email",
    "submission-adopter-phone",
    "submission-animal-name",
    "submission-date",
    "submission-status-display",
    "submission-address",
    "submission-garden",
    "submission-living",
    "submission-reason",
    "submission-extra-details"
  ];
  
  for (const elemId of requiredElements) {
    const elem = document.getElementById(elemId);
    if (!elem) {
      console.error(`Required element not found: ${elemId}`);
      alert(`Required element not found: ${elemId}. Please refresh the page.`);
      return;
    }
  }
  
  try {
    const response = await fetch(`../admin/rehome_submissions_api_detail.php?id=${id}`, {
      credentials: "include",
    });
    
    if (!response.ok) {
      throw new Error("Failed to load submission details");
    }
    
    const data = await response.json();
    
    // Update modal title
    const detailIdEl = document.getElementById("submission-detail-id");
    if (detailIdEl) detailIdEl.textContent = `#${id.toString().padStart(3, "0")}`;
    
    // Update fields - reuse adoption submission modal fields
    const adopterNameEl = document.getElementById("submission-adopter-name");
    if (adopterNameEl) adopterNameEl.textContent = data.owner_name || "Unknown";
    
    const adopterEmailEl = document.getElementById("submission-adopter-email");
    if (adopterEmailEl) adopterEmailEl.textContent = data.owner_email || "N/A";
    
    const adopterPhoneEl = document.getElementById("submission-adopter-phone");
    if (adopterPhoneEl) adopterPhoneEl.textContent = "-"; // Rehome doesn't have phone
    
    const animalNameEl = document.getElementById("submission-animal-name");
    if (animalNameEl) animalNameEl.textContent = `${data.pet_name} (${data.pet_type})`;
    
    const submissionDateEl = document.getElementById("submission-date");
    if (submissionDateEl) {
      const submissionDate = new Date(data.submitted_at);
      submissionDateEl.textContent = submissionDate.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
      });
    }
    
    // Status
    const statusDisplay = document.getElementById("submission-status-display");
    if (statusDisplay) {
      const status = (data.status || "submitted").toLowerCase();
      let statusClass = "";
      let statusText = "";
      
      if (status === "approved") {
        statusClass = "status-approved";
        statusText = "Approved";
      } else if (status === "rejected") {
        statusClass = "status-rejected";
        statusText = "Rejected";
      } else if (status === "in_review") {
        statusClass = "status-pending";
        statusText = "In Review";
      } else {
        statusClass = "status-pending";
        statusText = "Submitted";
      }
      
      statusDisplay.innerHTML = `<span class="${statusClass}">${statusText}</span>`;
    }
    
    // Address
    const addressEl = document.getElementById("submission-address");
    if (addressEl) {
      addressEl.textContent = 
        `${data.address_line1 || ""}${data.city ? ", " + data.city : ""}${data.postcode ? " (" + data.postcode + ")" : ""}`;
    }
    
    // Garden - not applicable for rehome, show spayed/neutered instead
    const gardenEl = document.getElementById("submission-garden");
    if (gardenEl) gardenEl.textContent = `Spayed/Neutered: ${data.spayed_neutered || "N/A"}`;
    
    // Living situation - show rehome reason
    const livingEl = document.getElementById("submission-living");
    if (livingEl) livingEl.textContent = `Reason: ${data.rehome_reason || "N/A"}`;
    
    // Reason for adoption - use pet story
    const reasonEl = document.getElementById("submission-reason");
    if (reasonEl) reasonEl.value = data.pet_story || "";
    
    // Hide Home Photos section for rehome submissions (only for adopt submissions)
    const homePhotosSection = document.getElementById('home-photos-section');
    if (homePhotosSection) {
      homePhotosSection.style.display = 'none';
    }
    
    // Additional details
    const extraDetails = document.getElementById("submission-extra-details");
    if (extraDetails) {
      extraDetails.innerHTML = `
        <div><strong>Pet Type:</strong> ${data.pet_type}</div>
        <div><strong>Breed:</strong> ${data.breed}</div>
        <div><strong>Age:</strong> ${data.age_years} years</div>
        <div><strong>Gender:</strong> ${data.gender}</div>
        <div><strong>Color:</strong> ${data.color}</div>
        <div><strong>Weight:</strong> ${data.weight} kg</div>
        <div><strong>Height:</strong> ${data.height} cm</div>
        ${data.pet_image_path ? `<div><strong>Pet Image:</strong> <img src="../${data.pet_image_path}" alt="Pet Image" style="max-width: 200px; margin-top: 8px; border-radius: 8px;" /></div>` : ""}
        ${data.documents && data.documents.length > 0 ? `<div><strong>Documents:</strong> ${data.documents.length} file(s)</div>` : ""}
      `;
    }
    
    // Show/hide approve/reject buttons for rehome submissions
    const approveBtn = document.getElementById("modal-approve-btn");
    const rejectBtn = document.getElementById("modal-reject-btn");
    
    if (approveBtn && rejectBtn) {
      const status = (data.status || "submitted").toLowerCase();
      const shouldShowButtons = status !== "approved" && status !== "rejected";
      
      // Set onclick handlers
      approveBtn.onclick = () => updateRehomeStatus(id, "approved");
      rejectBtn.onclick = () => updateRehomeStatus(id, "rejected");
      
      // Show/hide buttons
      if (shouldShowButtons) {
        approveBtn.style.display = "inline-block";
        rejectBtn.style.display = "inline-block";
      } else {
        approveBtn.style.display = "none";
        rejectBtn.style.display = "none";
      }
      
      console.log("Approve/Reject buttons configured for rehome submission:", {
        id: id,
        status: status,
        shouldShow: shouldShowButtons,
        approveDisplay: approveBtn.style.display,
        rejectDisplay: rejectBtn.style.display
      });
    } else {
      console.error("Approve/Reject buttons not found in modal!");
    }
    
    // Show modal with proper z-index and ensure visibility
    modal.style.display = "flex";
    modal.style.zIndex = "10000";
    modal.style.visibility = "visible";
    modal.style.opacity = "1";
    
    // Force reflow to ensure modal is visible
    void modal.offsetHeight;
    
    // Double check modal is visible
    const computedStyle = window.getComputedStyle(modal);
    console.log("Modal display:", computedStyle.display);
    console.log("Modal z-index:", computedStyle.zIndex);
    console.log("Modal visibility:", computedStyle.visibility);
    
    if (computedStyle.display === "none") {
      console.error("Modal still hidden after setting display to flex!");
      // Try alternative approach
      modal.classList.remove("hidden");
      modal.removeAttribute("hidden");
      modal.style.display = "flex !important";
    }
    
    console.log("Modal should be visible now");
  } catch (error) {
    console.error("Error loading rehome submission:", error);
    alert("Error loading submission details. Please try again.");
  }
};

// Function to update rehome status and create animal entry if approved
async function updateRehomeStatus(submissionId, newStatus) {
  // If approving, check if pet image exists first
  if (newStatus === "approved") {
    try {
      const detailResponse = await fetch(`../admin/rehome_submissions_api_detail.php?id=${submissionId}`, {
        credentials: "include",
      });
      
      if (detailResponse.ok) {
        const submission = await detailResponse.json();
        
        if (!submission.pet_image_path || submission.pet_image_path.trim() === "") {
          alert("Cannot approve this submission. Pet image is required. Please ask the submitter to add a photo.");
          return;
        }
      }
    } catch (error) {
      console.error("Error checking submission details:", error);
      alert("Error checking submission details. Please try again.");
      return;
    }
  }
  
  if (!confirm(`Are you sure you want to ${newStatus === "approved" ? "approve" : "reject"} this submission?`)) {
    return;
  }
  
  try {
    const response = await fetch("../admin/rehome_update.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({
        id: submissionId,
        status: newStatus,
      }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      // If approved, create animal entry in hewan table
      if (newStatus === "approved") {
        await createAnimalFromRehome(submissionId);
      }
      
      alert(`Submission ${newStatus === "approved" ? "approved" : "rejected"} successfully!`);
      
      // Close modal and reload submissions
      const modal = document.getElementById("submission-detail-modal");
      if (modal) modal.style.display = "none";
      
      // Reload rehome submissions
      await loadRehomeSubmissionsFromBackend();
      renderRehomeSubmissions("all");
    } else {
      alert("Error: " + (data.message || "Failed to update status"));
    }
  } catch (error) {
    console.error("Error updating status:", error);
    alert("Error updating status. Please try again.");
  }
}

// Function to create animal entry from approved rehome submission
async function createAnimalFromRehome(submissionId) {
  try {
    // First get the submission details
    const detailResponse = await fetch(`../admin/rehome_submissions_api_detail.php?id=${submissionId}`, {
      credentials: "include",
    });
    
    if (!detailResponse.ok) {
      throw new Error("Failed to load submission details");
    }
    
    const submission = await detailResponse.json();
    
    // Create animal entry
    const createResponse = await fetch("../admin/create_animal_from_rehome.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({
        submission_id: submissionId,
        pet_name: submission.pet_name,
        pet_type: submission.pet_type,
        breed: submission.breed,
        gender: submission.gender,
        age: submission.age_years + " years",
        color: submission.color,
        weight: submission.weight,
        height: submission.height,
        main_photo: submission.pet_image_path,
        description: submission.pet_story,
      }),
    });
    
    const createData = await createResponse.json();
    
    if (!createData.success) {
      console.error("Error creating animal:", createData.message);
      alert("Warning: Animal entry could not be created. " + (createData.message || ""));
    }
  } catch (error) {
    console.error("Error creating animal from rehome:", error);
    alert("Warning: Animal entry could not be created automatically.");
  }
}

// =========================================================
// --- BACKEND INTEGRATION: LOAD & RENDER USERS ---
// =========================================================
async function loadUsersFromBackend() {
  try {
    const resp = await fetch("../admin/users_api.php", {
      credentials: "include",
    });
    if (!resp.ok) throw new Error("Failed to load users");
    const json = await resp.json();
    if (json && Array.isArray(json.data)) {
      mockUsers = json.data.map((u) => ({
        id: u.id,
        name: u.name,
        email: u.email,
        role: u.role || "user",
        status: u.status || "active",
        registered: u.registered || null,
      }));
      console.log("Loaded users:", mockUsers.length);
    }
  } catch (e) {
    console.warn("Users API error:", e);
    // Leave mockUsers as-is
  }
}

function renderManageUsers() {
  const tbody = document.getElementById("users-table-body");
  if (!tbody) return;

  if (!mockUsers || mockUsers.length === 0) {
    tbody.innerHTML = `
      <tr class="empty-row">
        <td colspan="5" style="text-align: center; color: #888; padding: 40px; background: transparent; border-radius: 6px;">
          <i class="fas fa-users" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
          Tidak ada data user.
        </td>
      </tr>`;
    return;
  }

  const rows = mockUsers
    .map((u) => {
      const statusClass =
        u.status === "inactive"
          ? "status-rejected"
          : u.role === "admin"
          ? "status-approved"
          : "status-pending"; // default visual

      return `
        <tr>
          <td>${u.name || "-"}</td>
          <td>${u.email || "-"}</td>
          <td><span class="${statusClass}">${
        u.role ? u.role.toUpperCase() : u.status.toUpperCase()
      }</span></td>
          <td style="text-align:center;">
            <button class="btn btn-submit" title="Delete Account" onclick="deleteUser(${
              u.id
            })">Delete Account</button>
          </td>
        </tr>`;
    })
    .join("");

  tbody.innerHTML = rows;

  // Update summary numbers if present
  const totalUsersEl = document.querySelector(
    ".small-summary-card.first .small-number"
  );
  if (totalUsersEl) totalUsersEl.textContent = mockUsers.length;
  const totalOwnersEl = document.querySelector(
    ".summary-panel .small-summary-card:nth-child(2) .small-number"
  );
  if (totalOwnersEl)
    totalOwnersEl.textContent = mockUsers.filter(
      (u) => u.role === "owner"
    ).length;
  const totalAdoptersEl = document.querySelector(
    ".summary-panel .small-summary-card:nth-child(3) .small-number"
  );
  if (totalAdoptersEl)
    totalAdoptersEl.textContent = mockUsers.filter(
      (u) => u.role === "adopter"
    ).length;
}

window.renderManageUsers = renderManageUsers;

// --- DELETE USER ---
window.deleteUser = function (userId) {
  if (!userId || isNaN(userId)) return;
  if (!confirm("Are you sure you want to delete this account?")) return;
  fetch("../admin/delete_user.php", {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ id: userId }),
  })
    .then((r) => r.json())
    .then((json) => {
      if (json && json.success) {
        // Refresh list
        loadUsersFromBackend().then(() => {
          renderManageUsers();
          alert("Account deleted.");
        });
      } else {
        alert(json.message || "Failed to delete account");
      }
    })
    .catch((err) => {
      console.warn("Delete user error:", err);
      alert("Network/Server error while deleting account");
    });
};
