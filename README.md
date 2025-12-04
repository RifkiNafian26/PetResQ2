# PetResQ - Pet Adoption Platform

## Project Structure

```
PetResQ/
├── index.html                 # Landing page with login modal
├── login.html                 # Login page (legacy)
├── adopt.html                 # (Moved to adopt/ folder)
├── css/
│   └── style.css             # Global styles (navbar, modal, buttons, profile)
├── html/                      # Additional HTML files
├── icon/                      # Icon assets
├── js/
│   └── script.js             # Main application logic
├── adopt/
│   ├── adopt.html            # Adopt listing page with animal cards
│   ├── animalprofile.html    # Animal detail profile page
│   ├── styleadopt.css        # Adopt page styles
│   ├── styleprofile.css      # Animal profile page styles
│   └── profilescript.js      # Animal profile page logic
├── config.php                # Database configuration
├── login.php                 # Login API endpoint
├── register.php              # Register API endpoint
├── check_session.php         # Session check endpoint
└── logout.php                # Logout endpoint
```

## Key Features

### 1. Authentication System

- **Login/Register Modal**: Modal overlay on landing page (index.html)
- **Database**: MySQL table `user` with columns: id_user, nama, email, password
- **Session Management**: PHP sessions with cache-control headers
- **Error Handling**: AJAX form submission with error display (no page redirect)
- **Cross-page Login**: Login works on all pages (index.html and adopt/adopt.html)

### 2. User Profile Display

- **Profile Button**: Pill-shaped green button in navbar
- **Shows**: Full user name after login
- **Dropdown Menu**: Displays user name, email, and logout button
- **Auto-hide**: Dropdown closes when navigating via navbar links

### 3. Animal Adoption Listing

- **Location**: `adopt/adopt.html`
- **Features**:
  - Animal cards grid with 12 animals
  - Filter sidebar (Animal type, Breed, Color, Age)
  - Clickable cards that navigate to animal profile

### 4. Animal Profile Page (NEW)

- **Location**: `adopt/animalprofile.html`
- **Trigger**: Clicking any animal card on adopt page
- **Displays**:
  - Animal greeting ("Hi Human!")
  - Animal header with avatar, name, and pet ID
  - Main photo section
  - Image gallery (4 thumbnails)
  - Details grid: Gender, Breed, Age, Color, Weight, Height
  - Vaccination information table
  - Story section: Characteristics of the animal
  - "Start chat" button (contact owner)
  - "Get started" button (begin adoption)

## How It Works

### Authentication Flow

1. User clicks profile button on navbar
2. Login modal opens
3. User enters email and password
4. Form submits via AJAX to `login.php`
5. PHP validates and returns JSON response
6. On success: Page reloads and checks login status
7. On failure: Error message displays in modal
8. Once logged in: Profile button shows user name with dropdown menu

### Animal Profile Flow

1. User navigates to `adopt/adopt.html`
2. User views animal cards with information
3. User clicks any animal card
4. Click handler captures card index (1-12)
5. Page navigates to `animalprofile.html?id={index}`
6. `profilescript.js` loads animal data from database
7. Profile page populates with animal information
8. Animal name, details, story, and vaccination info display

## Important Utilities

### getPhpPath(phpFile)

Located in `js/script.js`, detects if page is in subdirectory and returns correct relative path:

- From `index.html`: `login.php`
- From `adopt/adopt.html`: `../login.php`

### checkUserLogin()

Runs on page load, fetches session status via `check_session.php` and displays appropriate UI (login button or profile name).

### setupAnimalCardListeners()

Adds click handlers to all animal cards on adopt.html. Each card click navigates to profile page with animal ID.

## Database Schema

### users table

```sql
CREATE TABLE user (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);
```

## API Endpoints

- **POST /login.php**: Login user (email, password)
- **POST /register.php**: Register new user (nama, email, password, confirm_password)
- **GET /check_session.php**: Check if user is logged in
- **GET /logout.php**: Logout user and redirect to index.html

## Styling

- **Primary Color**: #6e7b59 (Green)
- **Background**: #fefae0 (Cream)
- **Font**: Poppins (main), Kreon (headings)
- **Responsive**: Mobile-first design with breakpoints at 900px and 600px

## Future Enhancements

- [ ] Upload photos for animal profiles
- [ ] Chat functionality for "Start chat" button
- [ ] Adoption application form for "Get started" button
- [ ] Search functionality for animal cards
- [ ] Wishlist/favorites system
- [ ] Reviews and ratings for adopted animals
