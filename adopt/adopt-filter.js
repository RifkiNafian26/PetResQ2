// Dynamic filtering for adopt page
document.addEventListener("DOMContentLoaded", function () {
  const filterCheckboxes = document.querySelectorAll(
    '.filter-option input[type="checkbox"]'
  );
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");
  const animalGrid = document.getElementById("animal-grid");
  const resetButton = document.querySelector(".reset-filters");

  // Function to load animals with filters
  function loadAnimals() {
    // Collect selected filters
    const filters = {
      breed: [],
      color: [],
      age: [],
      animal: [],
      search: searchInput.value.trim(),
    };

    // Get checked breed filters
    document
      .querySelectorAll('input[name="breed"]:checked')
      .forEach((checkbox) => {
        filters.breed.push(checkbox.value);
      });

    // Get checked color filters
    document
      .querySelectorAll('input[name="color"]:checked')
      .forEach((checkbox) => {
        filters.color.push(checkbox.value);
      });

    // Get checked age filters
    document
      .querySelectorAll('input[name="age"]:checked')
      .forEach((checkbox) => {
        filters.age.push(checkbox.value);
      });

    // Get checked animal type filters (Dog/Cat/Rabbit)
    document
      .querySelectorAll('input[name="animal"]:checked')
      .forEach((checkbox) => {
        filters.animal.push(checkbox.value);
      });

    // Show loading state
    animalGrid.innerHTML =
      '<p style="text-align: center; width: 100%; padding: 20px;">Loading...</p>';

    // Build query string
    const queryParams = new URLSearchParams();

    if (filters.search) {
      queryParams.append("search", filters.search);
    }

    filters.breed.forEach((b) => queryParams.append("breed[]", b));
    filters.color.forEach((c) => queryParams.append("color[]", c));
    filters.age.forEach((a) => queryParams.append("age[]", a));
    // Pass animal type to backend if supported
    filters.animal.forEach((t) => queryParams.append("animal[]", t));

    // Fetch filtered animals
    fetch("get_animals.php?" + queryParams.toString())
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok: " + response.status);
        }
        return response.json().catch(() => {
          throw new Error("Invalid JSON from server");
        });
      })
      .then((animals) => {
        // Apply client-side filter for animal type if any selected
        if (Array.isArray(animals) && filters.animal.length > 0) {
          const wanted = new Set(
            filters.animal.map((v) => String(v).trim().toLowerCase())
          );
          animals = animals.filter((a) => {
            const jenis = (a.jenis || a.animal || "").trim().toLowerCase();
            return wanted.has(jenis);
          });
        }
        // If animals is not an array, treat as no matches
        if (!Array.isArray(animals)) {
          displayAnimals([]);
          return;
        }
        displayAnimals(animals);
        console.log("Animals loaded:", animals);
      })
      .catch((error) => {
        console.error("Error loading animals:", error);
        // Fallback to user-friendly empty state instead of error block
        animalGrid.innerHTML =
          '<p style="text-align: center; width: 100%; padding: 20px;">No animals match your filters.</p>';
      });
  }

  // Function to display animals
  function displayAnimals(animals) {
    if (animals.length === 0) {
      animalGrid.innerHTML =
        '<p style="text-align: center; width: 100%; padding: 20px;">No animals match your filters.</p>';
      return;
    }

    let html = "";
    animals.forEach((animal) => {
      // Check status case-insensitively to handle variations
      const animalStatus = (animal.status || '').toString().trim();
      const isAdopted = animalStatus.toLowerCase() === 'adopted';
      let photo = animal.main_photo || "";
      // Adjust path if it starts with uploads/
      if (photo && photo.startsWith("uploads/")) {
        photo = "../" + photo;
      }
      const imgTag = photo
        ? `<img src="${photo}" alt="${animal.namaHewan}" onerror="this.parentElement.innerHTML='<div style=&quot;display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background-color: #e0e0e0; font-size: 12px; color: #666;&quot;>No Image</div>'" style="width: 100%; height: 100%; object-fit: cover; ${isAdopted ? 'opacity: 0.5; filter: grayscale(50%);' : ''}">`
        : `<div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background-color: #e0e0e0; font-size: 12px; color: #666; ${isAdopted ? 'opacity: 0.5;' : ''}">No Image</div>`;
      
      html += `
        <div class="animal-card ${isAdopted ? 'adopted-card' : ''}" data-id="${animal.id_hewan}" data-animal="${
        animal.jenis || ""
      }" data-breed="${animal.breed}" data-color="${animal.color}" data-age="${
        animal.age
      }" data-status="${animalStatus}">
          ${isAdopted ? '<div style="position: absolute; top: 10px; right: 10px; background: #d9534f; color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; z-index: 20; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">ADOPTED</div>' : ''}
          <div class="animal-photo">
            ${imgTag}
          </div>
          <div class="animal-info">
            <div class="animal-name">${animal.namaHewan}</div>
            <div class="animal-description">${animal.breed} - ${
        animal.color
      }, ${animal.age}</div>
            <div class="animal-gender">${animal.gender}</div>
            ${isAdopted ? '<div style="margin-top: 8px; padding: 4px 8px; background: rgba(217, 83, 79, 0.1); color: #d9534f; border-radius: 5px; font-size: 11px; font-weight: 600; text-align: center;">Already Adopted</div>' : ''}
          </div>
        </div>
      `;
    });
    animalGrid.innerHTML = html;

    // Add click event listeners to animal cards
    document.querySelectorAll(".animal-card").forEach((card) => {
      card.addEventListener("click", function (e) {
        const status = (this.getAttribute("data-status") || '').toString().trim().toLowerCase();
        const isAdopted = status === 'adopted';
        
        if (isAdopted) {
          e.preventDefault();
          e.stopPropagation();
          alert("This animal has already been adopted.");
          return false;
        }
        
        const animalId = this.getAttribute("data-id");
        // Navigate to animal profile page only if not adopted
        window.location.href = "animalprofile.php?id=" + animalId;
      });
    });
  }

  // Add event listeners to filter checkboxes
  filterCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      loadAnimals();
    });
  });

  // Add event listener to search form
  searchForm.addEventListener("submit", function (e) {
    e.preventDefault();
    loadAnimals();
  });

  // Add event listener to search input (live search)
  let searchTimeout;
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      loadAnimals();
    }, 500); // Wait 500ms after user stops typing
  });

  // Reset filters
  resetButton.addEventListener("click", function () {
    // Uncheck all filters
    filterCheckboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });

    // Clear search
    searchInput.value = "";

    // Reload all animals
    loadAnimals();
  });

  // Load animals on page load
  loadAnimals();
});
