document.addEventListener("DOMContentLoaded", () => {
  const formSteps = document.querySelectorAll(".form-step");
  const stepIndicators = document.querySelectorAll(".progress-bar .step");
  const nextButtons = document.querySelectorAll(".next-step");
  const backButtons = document.querySelectorAll(".back-step");
  let currentStep = 1;
  // Session user info
  let __userName = null;
  let __userEmail = null;
  // Selected pet id (id_hewan)
  let __hewanId = null;

  // Read id_hewan from query string if provided
  try {
    const params = new URLSearchParams(window.location.search);
    const idParam = params.get("id_hewan") || params.get("id") || "";
    if (idParam) {
      const asNum = parseInt(idParam, 10);
      __hewanId = Number.isFinite(asNum) ? asNum : null;
    }
  } catch (e) {
    /* ignore */
  }

  // Populate Start step profile box from session
  function populateStartProfile() {
    const emailEl = document.getElementById("adopt-user-email");
    const nameEl = document.getElementById("adopt-user-name");
    if (!emailEl || !nameEl) return;

    const name = __userName || "-";
    const email = __userEmail || "-";

    emailEl.textContent = email;
    nameEl.textContent = name;
  }

  // Check user login status and populate start section
  function checkUserLogin() {
    // Get correct path for check_session.php
    const sessionPath = typeof getPhpPath === "function"
      ? getPhpPath("check_session.php")
      : "../check_session.php";
    
    fetch(sessionPath)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.is_logged_in) {
          __userName = data.user_name || "-";
          __userEmail = data.user_email || "-";
          populateStartProfile();
        } else {
          populateStartProfile(); // Show "-" if not logged in
        }
      })
      .catch((error) => {
        console.error("Error checking login:", error);
        populateStartProfile(); // Show "-" on error
      });
  }

  // Function to populate review data
  function populateReviewData() {
    // Personal Information
    const name = __userName || "";
    const email = __userEmail || "";
    const displayName = (name || "-").trim();
    const nameEl = document.getElementById("review-name");
    const emailEl = document.getElementById("review-email");
    if (nameEl) nameEl.textContent = displayName;
    if (emailEl) emailEl.textContent = email || "-";

    // Address Information
    const address = document.querySelector("#address")?.value || "-";
    const postcode = document.querySelector("#postcode")?.value || "-";
    const telephone = document.querySelector("#telephone")?.value || "-";
    document.getElementById("review-address").textContent = address;
    document.getElementById("review-postcode").textContent = postcode;
    document.getElementById("review-telephone").textContent = telephone;

    // Home Information
    const garden =
      document.querySelector('input[name="garden"]:checked')?.value || "-";
    const living = document.querySelector("#living-situation")?.value || "-";
    const householdSetting =
      document.querySelector("#household-setting")?.value || "-";
    const householdActivity =
      document.querySelector("#household-activity")?.value || "-";
    document.getElementById("review-garden").textContent = garden;
    document.getElementById("review-living").textContent = living;
    document.getElementById("review-household-setting").textContent =
      householdSetting;
    document.getElementById("review-household-activity").textContent =
      householdActivity;

    // Roommate Information
    const adults = document.querySelector("#adults")?.value || "-";
    const children = document.querySelector("#children")?.value || "-";
    const childrenAges = document.querySelector("#age-youngest")?.value || "-";
    const visitingChildren =
      document.querySelector('input[name="visiting-children"]:checked')
        ?.value || "-";
    const visitingAges = document.querySelector("#ages-visiting")?.value || "-";
    const flatmates =
      document.querySelector('input[name="flatmates"]:checked')?.value || "-";
    const flatmatesConsent =
      document.querySelector("#flatmates-consent")?.value || "-";

    document.getElementById("review-adults").textContent = adults;
    document.getElementById("review-children").textContent = children;

    // Format children ages
    let childrenAgesText = "-";
    if (childrenAges && childrenAges !== "no-children") {
      const ageOptions = {
        "0-5": "0-5 years",
        "6-12": "6-12 years",
        "13-17": "13-17 years",
      };
      childrenAgesText = ageOptions[childrenAges] || childrenAges;
    } else if (childrenAges === "no-children") {
      childrenAgesText = "No children";
    }
    document.getElementById("review-children-ages").textContent =
      childrenAgesText;

    document.getElementById("review-visiting-children").textContent =
      visitingChildren;

    // Show/hide visiting ages based on visiting children
    const visitingAgesItem = document.getElementById(
      "review-visiting-ages-item"
    );
    if (visitingChildren === "Yes" && visitingAges) {
      const visitingAgesOptions = {
        under5: "Under 5 years",
        over5: "Over 5 years",
        all: "All ages",
      };
      document.getElementById("review-visiting-ages").textContent =
        visitingAgesOptions[visitingAges] || visitingAges;
      visitingAgesItem.style.display = "block";
    } else {
      visitingAgesItem.style.display = "none";
    }

    document.getElementById("review-flatmates").textContent = flatmates;

    // Show/hide flatmates consent based on flatmates
    const flatmatesConsentItem = document.getElementById(
      "review-flatmates-consent-item"
    );
    if (flatmates === "Yes" && flatmatesConsent) {
      document.getElementById("review-flatmates-consent").textContent =
        flatmatesConsent;
      flatmatesConsentItem.style.display = "block";
    } else {
      flatmatesConsentItem.style.display = "none";
    }

    // Other Animals Information
    const allergies = document.querySelector("#allergies")?.value || "-";
    const otherAnimals =
      document.querySelector('input[name="other-animals"]:checked')?.value ||
      "-";
    const vaccinated =
      document.querySelector('input[name="vaccinated"]:checked')?.value || "-";
    const experience = document.querySelector("#experience")?.value || "-";

    document.getElementById("review-allergies").textContent = allergies;
    document.getElementById("review-other-animals").textContent = otherAnimals;
    document.getElementById("review-vaccinated").textContent = vaccinated;
    document.getElementById("review-experience").textContent = experience;
  }

  // Function to update the view
  function updateSteps() {
    // Hide all steps
    formSteps.forEach((step) => {
      step.classList.remove("active");
    });

    // Show the current step
    const activeStepContent = document.getElementById(`step-${currentStep}`);
    if (activeStepContent) {
      activeStepContent.classList.add("active");
    }

    // Populate review data when entering step 7
    if (currentStep === 7) {
      populateReviewData();
    }

    // Hide back button on step 8 (thank you page)
    const backButtons = document.querySelectorAll(".back-step");
    backButtons.forEach((btn) => {
      if (currentStep === 8) {
        btn.style.display = "none";
      } else {
        btn.style.display = "";
      }
    });

    // Update progress bar
    stepIndicators.forEach((indicator) => {
      const stepNumber = parseInt(indicator.getAttribute("data-step"));
      indicator.classList.remove("current-step", "completed");

      if (stepNumber === currentStep) {
        // reposition rabbit if present and trigger a short jump animation
        try {
          if (window.__positionRabbit) window.__positionRabbit(currentStep);
          const rb = document.querySelector(".rabbit");
          if (rb) {
            rb.classList.remove("jump");
            // force reflow so animation can restart
            void rb.offsetWidth;
            rb.classList.add("jump");
          }
        } catch (e) {
          /* ignore */
        }
        indicator.classList.add("current-step");
      } else if (stepNumber < currentStep) {
        indicator.classList.add("completed");
      }
    });

    // Update progress line width based on completed steps
    const progressBar = document.querySelector(".progress-bar");
    if (progressBar) {
      const totalSteps = stepIndicators.length;
      const completedSteps = currentStep - 1; // Steps before current are completed
      const progressPercentage = (completedSteps / (totalSteps - 1)) * 100;
      progressBar.style.setProperty(
        "--progress-width",
        `${progressPercentage}%`
      );
    }
  }

  // Handle 'Continue' / 'Next' button clicks
  nextButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      // require step-level validation before moving forward
      if (!validateBeforeNext(currentStep)) return;

      // Special handling for step 7 - send button should not use next-step behavior
      if (
        currentStep === 7 &&
        button.classList.contains("send-application-button")
      ) {
        // This will be handled by the send button handler below
        return;
      }

      if (currentStep < formSteps.length) {
        currentStep++;
        updateSteps();
      }
    });
  });

  // Build payload from current form values
  function buildApplicationPayload() {
    const getVal = (sel) => document.querySelector(sel)?.value || "";
    const getRadio = (name) =>
      document.querySelector(`input[name="${name}"]:checked`)?.value || "";

    // Get home photos as base64 array
    const homePhotos = [];
    const photoInputs = document.querySelectorAll("input.home-photo-input");
    photoInputs.forEach((input) => {
      if (input.files && input.files.length > 0) {
        const file = input.files[0];
        // Convert to base64
        const reader = new FileReader();
        reader.onload = function(e) {
          homePhotos.push(e.target.result);
        };
        reader.readAsDataURL(file);
      }
    });

    return {
      // Selected animal to adopt
      hewan_id: __hewanId,
      id_hewan: __hewanId, // accept both keys for backend flexibility
      address: getVal("#address").trim(),
      postcode: getVal("#postcode").trim(),
      telephone: getVal("#telephone").trim(),

      garden: getRadio("garden"),
      living_situation: getVal("#living-situation").trim(),
      household_setting: getVal("#household-setting").trim(),
      household_activity: getVal("#household-activity").trim(),

      adults: parseInt(getVal("#adults") || "0", 10) || 0,
      children: parseInt(getVal("#children") || "0", 10) || 0,
      children_ages: getVal("#age-youngest") || null,
      visiting_children: getRadio("visiting-children") || null,
      visiting_ages: getVal("#ages-visiting") || null,
      flatmates: getRadio("flatmates") || null,
      flatmates_consent: getVal("#flatmates-consent") || null,

      allergies: getVal("#allergies").trim(),
      other_animals: getRadio("other-animals"),
      vaccinated: getRadio("vaccinated"),
      experience: getVal("#experience").trim(),
      home_photos: homePhotos, // Array of base64 encoded photos
    };
  }

  async function submitApplication(sendButton) {
    // Build payload with async photo conversion
    const getVal = (sel) => document.querySelector(sel)?.value || "";
    const getRadio = (name) =>
      document.querySelector(`input[name="${name}"]:checked`)?.value || "";

    // Get home photos as base64 array (async)
    const homePhotos = [];
    const photoInputs = Array.from(document.querySelectorAll("input.home-photo-input"));
    
    // Convert all photos to base64
    const photoPromises = photoInputs.map((input) => {
      return new Promise((resolve) => {
        if (input.files && input.files.length > 0) {
          const file = input.files[0];
          const reader = new FileReader();
          reader.onload = function(e) {
            resolve(e.target.result);
          };
          reader.onerror = () => resolve(null);
          reader.readAsDataURL(file);
        } else {
          resolve(null);
        }
      });
    });

    const photoResults = await Promise.all(photoPromises);
    photoResults.forEach((photo) => {
      if (photo) homePhotos.push(photo);
    });

    const payload = {
      // Selected animal to adopt
      hewan_id: __hewanId,
      id_hewan: __hewanId,
      address: getVal("#address").trim(),
      postcode: getVal("#postcode").trim(),
      telephone: getVal("#telephone").trim(),

      garden: getRadio("garden"),
      living_situation: getVal("#living-situation").trim(),
      household_setting: getVal("#household-setting").trim(),
      household_activity: getVal("#household-activity").trim(),

      adults: parseInt(getVal("#adults") || "0", 10) || 0,
      children: parseInt(getVal("#children") || "0", 10) || 0,
      children_ages: getVal("#age-youngest") || null,
      visiting_children: getRadio("visiting-children") || null,
      visiting_ages: getVal("#ages-visiting") || null,
      flatmates: getRadio("flatmates") || null,
      flatmates_consent: getVal("#flatmates-consent") || null,

      allergies: getVal("#allergies").trim(),
      other_animals: getRadio("other-animals"),
      vaccinated: getRadio("vaccinated"),
      experience: getVal("#experience").trim(),
      home_photos: homePhotos, // Array of base64 encoded photos
    };

    const sessionPath =
      typeof getPhpPath === "function"
        ? getPhpPath("submit_adoption.php")
        : "../submit_adoption.php";

    const original = sendButton.innerHTML;
    sendButton.disabled = true;
    sendButton.innerHTML =
      '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    try {
      const res = await fetch(sessionPath, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const j = await res.json().catch(() => ({}));
      if (!res.ok || !j.ok) {
        throw new Error(j.error || `Submit failed (${res.status})`);
      }
      // Success → go to Thank You step
      currentStep = 8;
      updateSteps();
    } catch (err) {
      alert("Failed to submit application: " + (err.message || err));
    } finally {
      sendButton.disabled = false;
      sendButton.innerHTML = original;
    }
  }

  // Handle Send Application button (step 7)
  document.addEventListener("click", (e) => {
    if (e.target.closest(".send-application-button")) {
      e.preventDefault();

      // Validate checkboxes
      const confirmData = document.getElementById("confirm-data");
      const confirmCommitment = document.getElementById("confirm-commitment");

      if (!confirmData || !confirmData.checked) {
        alert("Please confirm that all information is accurate and complete.");
        if (confirmData) confirmData.focus();
        return;
      }

      if (!confirmCommitment || !confirmCommitment.checked) {
        alert("Please confirm your commitment to providing a loving home.");
        if (confirmCommitment) confirmCommitment.focus();
        return;
      }

      // Validate all form data before sending
      if (!validateBeforeNext(7)) return;

      // Real API call
      const sendButton = e.target.closest(".send-application-button");
      submitApplication(sendButton);
    }
  });

  // Handle 'Back' button clicks
  backButtons.forEach((button) => {
    button.addEventListener("click", () => {
      // Don't allow going back from thank you page (step 8)
      if (currentStep === 8) {
        return;
      }
      if (currentStep > 1) {
        currentStep--;
        updateSteps();
      }
    });
  });

  // --- Rabbit indicator: create element and position it above the progress bar ---
  (function setupRabbit() {
    const bar = document.querySelector(".progress-bar");
    if (!bar) return;

    // avoid creating it twice
    if (bar.querySelector(".rabbit")) return;

    const rabbit = document.createElement("div");
    rabbit.className = "rabbit";
    // load kelinci.jpg from root directory
    rabbit.innerHTML =
      '<div class="rabbit-inner"><img src="kelinci.jpg" alt="kelinci"/></div>';
    bar.appendChild(rabbit);

    function positionRabbit(stepNum) {
      const steps = Array.from(bar.querySelectorAll(".step"));
      const target = steps.find(
        (s) => parseInt(s.getAttribute("data-step")) === stepNum
      );
      if (!target) return;

      const barRect = bar.getBoundingClientRect();
      const tRect = target.getBoundingClientRect();
      const centerX =
        tRect.left - barRect.left + tRect.width / 2 - rabbit.offsetWidth / 2;
      rabbit.style.transform = `translateX(${Math.round(centerX)}px)`;
    }

    window.__positionRabbit = positionRabbit;

    // Position rabbit on initial load
    positionRabbit(1);
  })();

  // Initialize the first step view (after rabbit exists)
  updateSteps();

  // Fetch session and populate Start/Review user info
  (function fetchSessionUser() {
    try {
      // Get correct path for check_session.php
      let sessionPath = "../check_session.php";
      
      // Try to use getPhpPath if available (from ../js/script.js)
      if (typeof getPhpPath === "function") {
        try {
          sessionPath = getPhpPath("check_session.php");
        } catch (e) {
          // Fallback to relative path if getPhpPath fails
          sessionPath = "../check_session.php";
        }
      }
      
      fetch(sessionPath)
        .then((r) => {
          if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
          }
          return r.json();
        })
        .then((data) => {
          if (data && data.is_logged_in) {
            __userName = data.user_name || null;
            __userEmail = data.user_email || null;
            populateStartProfile();
          }
        })
        .catch((e) => {
          console.error("session fetch error", e);
          // Still populate profile with "-" on error
          populateStartProfile();
        });
    } catch (e) {
      console.error("session setup error", e);
      populateStartProfile();
    }
  })();

  // ---------------- Upload box click + preview handlers -----------------
  function setupUploadBoxes() {
    document
      .querySelectorAll(".upload-box, .image-upload-box")
      .forEach((box) => {
        // prefer input with class home-photo-input, fallback to any file input inside
        const input = box.querySelector(
          "input.home-photo-input, input[type=file]"
        );
        if (!input) return;

        // clicking the visual box should open file picker
        box.style.cursor = "pointer";
        box.addEventListener("click", (e) => {
          // avoid triggering when clicking on child controls
          if (e.target && e.target.tagName === "INPUT") return;
          input.click();
        });

        // when file selected, show preview and mark as has-file
        input.addEventListener("change", () => {
          if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = (ev) => {
              // set background image for the box for a clean preview
              box.style.backgroundImage = `url(${ev.target.result})`;
              box.style.backgroundSize = "cover";
              box.style.backgroundPosition = "center";
              box.classList.add("has-file");
            };
            reader.readAsDataURL(file);
          } else {
            box.classList.remove("has-file");
            box.style.backgroundImage = "";
          }
        });
      });
  }

  setupUploadBoxes();

  // ---------------- Roommate helpers (show/hide ages for visiting children) -----------------
  function setupRoommateControls() {
    // visiting-children radios (both pages share same name)
    document
      .querySelectorAll('input[name="visiting-children"]')
      .forEach((radio) => {
        radio.addEventListener("change", () => {
          const val = document.querySelector(
            'input[name="visiting-children"]:checked'
          );
          const wrapper1 = document.querySelector("#visiting-ages-wrapper");
          if (wrapper1)
            wrapper1.style.display = val && val.value === "Yes" ? "" : "none";
        });
      });

    // sync number-of-children -> age selection (show '-' when zero)
    const childInputs = ["#children", "#num-children"];
    childInputs.forEach((sel) => {
      const el = document.querySelector(sel);
      if (!el) return;
      el.addEventListener("input", () => {
        const value = parseInt(el.value || "0", 10) || 0;
        // find associated age select on the same page
        const page = el.closest(".form-step") || document;
        const ageSelect = page.querySelector("#age-youngest, #youngest-age");
        if (ageSelect) {
          if (value === 0) {
            // when zero children, select explicit 'no-children' sentinel value if it exists
            if (ageSelect.querySelector('option[value="no-children"]')) {
              ageSelect.value = "no-children";
            }
          } else {
            // if previously set to 'no-children', change to the first actual age range
            if (ageSelect.value === "no-children") {
              const firstRange = Array.from(ageSelect.options).find(
                (o) => o.value && o.value !== "no-children"
              );
              if (firstRange) ageSelect.value = firstRange.value;
            }
          }
        }
      });
    });

    // run once on load: apply visiting children state and child counts
    const visChecked = document.querySelector(
      'input[name="visiting-children"]:checked'
    );
    const visitingWrapper = document.querySelector("#visiting-ages-wrapper");
    if (visitingWrapper)
      visitingWrapper.style.display =
        visChecked && visChecked.value === "Yes" ? "" : "none";

    // handle flatmates -> show/hide follow-up consent question
    document.querySelectorAll('input[name="flatmates"]').forEach((radio) => {
      radio.addEventListener("change", () => {
        const val = document.querySelector('input[name="flatmates"]:checked');
        const wrapper = document.querySelector("#flatmates-consent-wrapper");
        if (wrapper)
          wrapper.style.display = val && val.value === "Yes" ? "" : "none";
      });
    });

    const flatChecked = document.querySelector(
      'input[name="flatmates"]:checked'
    );
    const flatWrapper = document.querySelector("#flatmates-consent-wrapper");
    if (flatWrapper)
      flatWrapper.style.display =
        flatChecked && flatChecked.value === "Yes" ? "" : "none";

    childInputs.forEach((sel) => {
      const el = document.querySelector(sel);
      if (el) {
        const ev = new Event("input");
        el.dispatchEvent(ev);
      }
    });
  }

  setupRoommateControls();

  // -- helpers to mark/clear invalid fields -----------------------------
  function markInvalid(el) {
    if (!el) return;
    el.classList.add("field-error");
  }
  function clearInvalid(el) {
    if (!el) return;
    el.classList.remove("field-error");
  }

  // Validate required inputs per-step. Return true to allow moving forward.
  function validateBeforeNext(step) {
    try {
      // Step 2: Address — require address, postcode, telephone (address2 & town removed)
      if (step === 2) {
        const a1 =
          document.querySelector("#address") ||
          document.querySelector("#address1") ||
          document.querySelector("#addr-line1");
        const postcode = document.querySelector("#postcode");
        const phone =
          document.querySelector("#telephone") ||
          document.querySelector("#tel-number");

        const fields = [a1, postcode, phone];
        let ok = true;
        fields.forEach((f) => {
          clearInvalid(f);
          if (!f || (f.value || "").trim() === "") {
            ok = false;
            markInvalid(f);
          }
        });
        if (!ok) {
          alert(
            "Please complete the required address fields (Address, Postcode, Telephone) before continuing."
          );
          const first = fields.find((f) => f && (f.value || "").trim() === "");
          if (first && first.focus) first.focus();
        }
        return ok;
      }

      // Step 3: Home — require garden radio and living/home fields
      if (step === 3) {
        const garden = document.querySelector('input[name="garden"]:checked');
        const living = document.querySelector("#living-situation");
        const setting = document.querySelector("#household-setting");
        const activity = document.querySelector("#household-activity");

        let ok = true;
        [living, setting, activity].forEach((f) => {
          clearInvalid(f);
          if (!f || (f.value || "").trim() === "") {
            ok = false;
            markInvalid(f);
          }
        });
        if (!garden) ok = false;

        if (!ok) {
          alert("Please complete the required home details before continuing.");
          if (!garden) {
            const g = document.querySelector('input[name="garden"]');
            if (g && g.focus) g.focus();
          } else {
            const first = [living, setting, activity].find(
              (x) => x && (x.value || "").trim() === ""
            );
            if (first && first.focus) first.focus();
          }
        }
        return ok;
      }

      // Step 4: Home Picture — require at least 2 images selected
      if (step === 4) {
        const inputs = Array.from(
          document.querySelectorAll("input.home-photo-input, input[type=file]")
        ).filter(Boolean);
        let count = 0;
        inputs.forEach((i) => {
          if (i.files && i.files.length > 0) count++;
        });
        if (count < 2) {
          alert("Please add at least 2 photos of your home before continuing.");
          return false;
        }
        return true;
      }

      // Step 5: Roommate — require at least one adult (children allowed to be zero/empty)
      if (step === 5) {
        const adults =
          document.querySelector("#adults") ||
          document.querySelector("#num-adults");
        let ok = true;
        if (!adults || adults.value === "" || parseInt(adults.value) < 1) {
          ok = false;
          markInvalid(adults);
        } else clearInvalid(adults);
        if (!ok) {
          alert(
            "Please complete roommate information (at least 1 adult) before continuing."
          );
        }
        return ok;
      }

      // Step 6: Other Animals — require allergies and radios
      if (step === 6) {
        const allergies = document.querySelector("#allergies");
        const otherAnimals = document.querySelector(
          'input[name="other-animals"]:checked'
        );
        const vaccinated = document.querySelector(
          'input[name="vaccinated"]:checked'
        );

        let ok = true;
        if (!allergies || (allergies.value || "").trim() === "") {
          ok = false;
          markInvalid(allergies);
        } else clearInvalid(allergies);
        if (!otherAnimals) ok = false;
        if (!vaccinated) ok = false;

        if (!ok) {
          alert("Please complete the Other Animals section before continuing.");
        }
        return ok;
      }

      // Step 7: Confirm — validate all previous steps are complete
      if (step === 7) {
        // Check that all required fields from previous steps are filled
        const address = document.querySelector("#address")?.value?.trim();
        const postcode = document.querySelector("#postcode")?.value?.trim();
        const telephone = document.querySelector("#telephone")?.value?.trim();
        const garden = document.querySelector('input[name="garden"]:checked');
        const living = document
          .querySelector("#living-situation")
          ?.value?.trim();
        const setting = document
          .querySelector("#household-setting")
          ?.value?.trim();
        const activity = document
          .querySelector("#household-activity")
          ?.value?.trim();
        const adults = document.querySelector("#adults")?.value;
        const allergies = document.querySelector("#allergies")?.value?.trim();
        const otherAnimals = document.querySelector(
          'input[name="other-animals"]:checked'
        );
        const vaccinated = document.querySelector(
          'input[name="vaccinated"]:checked'
        );

        let ok = true;
        let missingFields = [];

        if (!address) missingFields.push("Address");
        if (!postcode) missingFields.push("Postcode");
        if (!telephone) missingFields.push("Telephone");
        if (!garden) missingFields.push("Garden information");
        if (!living) missingFields.push("Living situation");
        if (!setting) missingFields.push("Household setting");
        if (!activity) missingFields.push("Household activity");
        if (!adults || parseInt(adults) < 1)
          missingFields.push("Number of adults");
        if (!allergies) missingFields.push("Allergies information");
        if (!otherAnimals) missingFields.push("Other animals information");
        if (!vaccinated) missingFields.push("Vaccination information");

        // Check photos
        const photoInputs = Array.from(
          document.querySelectorAll("input.home-photo-input")
        );
        const photoCount = photoInputs.filter(
          (i) => i.files && i.files.length > 0
        ).length;
        if (photoCount < 2)
          missingFields.push("Home photos (minimum 2 required)");

        if (missingFields.length > 0) {
          ok = false;
          alert(
            "Please complete all required fields before submitting:\n\n" +
              missingFields.join("\n")
          );
        }

        return ok;
      }

      return true;
    } catch (err) {
      console.error("validateBeforeNext error", err);
      return true;
    }
  }

  // --- numeric-only helper -------------------------------------------------
  // Make sure text inputs with class 'numeric-only' only accept digits.
  function sanitizeToDigits(str) {
    return str.replace(/\D+/g, "");
  }

  function attachNumericOnly(selector) {
    document.querySelectorAll(selector).forEach((el) => {
      // sanitize existing value on load
      el.value = sanitizeToDigits(el.value || "");

      // on input: remove non-digit characters
      el.addEventListener("input", (e) => {
        const cleaned = sanitizeToDigits(e.target.value);
        if (e.target.value !== cleaned) e.target.value = cleaned;
      });

      // on keypress: prevent non-digit keystrokes but allow control/navigation
      el.addEventListener("keypress", (e) => {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (
          !/\d/.test(char) &&
          !e.ctrlKey &&
          !e.metaKey &&
          e.key !== "Backspace"
        ) {
          e.preventDefault();
        }
      });

      // on paste: sanitize pasted text
      el.addEventListener("paste", (e) => {
        const text =
          (e.clipboardData || window.clipboardData).getData("text") || "";
        if (/\D/.test(text)) {
          e.preventDefault();
          const cleaned = sanitizeToDigits(text);
          // insert cleaned text at cursor
          const start = el.selectionStart || 0;
          const end = el.selectionEnd || 0;
          const newVal =
            el.value.slice(0, start) + cleaned + el.value.slice(end);
          el.value = sanitizeToDigits(newVal);
        }
      });
    });
  }

  // Attach to elements marked .numeric-only
  attachNumericOnly(".numeric-only");

  // Check user login status on page load
  checkUserLogin();
});
