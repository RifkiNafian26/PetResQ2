// Animal Profile Page Script

// Get animal ID from URL query parameter
function getAnimalFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

// Sample animal data
const animalDatabase = {
  1: {
    name: "Rex",
    id: "3210283",
    breed: "Holland Hop",
    gender: "Male",
    age: "2 years",
    color: "Golden",
    weight: "1.5 kg",
    height: "20 cm",
    story:
      "A friendly and curious rabbit who loves to play and explore. Rex is well-socialized and enjoys human interaction.",
  },
  2: {
    name: "Luna",
    id: "3210284",
    breed: "British Shorthair",
    gender: "Female",
    age: "1 year",
    color: "Gray",
    weight: "4 kg",
    height: "25 cm",
    story:
      "Luna is a calm and affectionate cat. She loves to cuddle and is perfect for a quiet family home.",
  },
  3: {
    name: "Max",
    id: "3210285",
    breed: "Golden Retriever",
    gender: "Male",
    age: "3 years",
    color: "Golden",
    weight: "30 kg",
    height: "55 cm",
    story:
      "Max is an energetic and loyal dog. He loves to play fetch and is great with families and children.",
  },
  4: {
    name: "Bella",
    id: "3210286",
    breed: "Poodle",
    gender: "Female",
    age: "5 years",
    color: "White",
    weight: "6 kg",
    height: "30 cm",
    story:
      "Bella is a smart and gentle senior poodle. She enjoys slow walks and gentle petting.",
  },
  5: {
    name: "Charlie",
    id: "3210287",
    breed: "Siberian Husky",
    gender: "Male",
    age: "2 years",
    color: "Gray",
    weight: "25 kg",
    height: "50 cm",
    story:
      "Charlie is an adventurous husky who loves outdoor activities. He needs an active family.",
  },
  6: {
    name: "Daisy",
    id: "3210288",
    breed: "Bengal",
    gender: "Female",
    age: "1 year",
    color: "Orange",
    weight: "3.5 kg",
    height: "22 cm",
    story:
      "Daisy is an active and playful Bengal cat. She loves climbing and interactive play.",
  },
};

// Load animal data into page
function loadAnimalProfile() {
  const animalId = getAnimalFromURL();

  if (!animalId || !animalDatabase[animalId]) {
    // Redirect to adopt page if no valid ID
    window.location.href = "adopt.html";
    return;
  }

  const animal = animalDatabase[animalId];

  // Update page elements
  document.getElementById("profile-name").textContent = animal.name;
  document.getElementById("profile-id").textContent = `Pet ID: ${animal.id}`;

  // Update details grid
  const detailItems = document.querySelectorAll(".detail-item");
  const detailValues = [
    animal.gender,
    animal.breed,
    animal.age,
    animal.color,
    animal.weight,
    animal.height,
  ];

  detailItems.forEach((item, index) => {
    const label = item.querySelector(".detail-label");
    if (label && detailValues[index]) {
      label.textContent = detailValues[index];
    }
  });

  // Update story
  document.getElementById("profile-story").innerHTML = `
    <div class="story-item">
      <i data-feather="eye"></i>
      <span>Can live with other children of any age</span>
    </div>
    <div class="story-item">
      <i data-feather="shield"></i>
      <span>Vaccinated</span>
    </div>
    <div class="story-item">
      <i data-feather="home"></i>
      <span>House-Trained</span>
    </div>
    <div class="story-item">
      <i data-feather="lock"></i>
      <span>Neutered</span>
    </div>
    <div class="story-item">
      <i data-feather="camera"></i>
      <span>Shots up to date</span>
    </div>
  `;

  // Reinitialize feather icons
  if (typeof feather !== "undefined") {
    feather.replace();
  }
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  loadAnimalProfile();
});
