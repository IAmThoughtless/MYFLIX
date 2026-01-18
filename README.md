# 🎬 MYFLIX — Movie & TV Discovery Platform

**MYFLIX** is a full-stack, Netflix-inspired web application that enables users to browse trending movies and TV shows, explore cinematic details, and curate a personalized watchlist.  
It integrates **The Movie Database (TMDB) API** for real-time content and uses a **MySQL backend** for secure user management and saved titles.

---

## 🚀 Key Features

### 🎬 Content & Discovery
- **Dynamic Landing Page**  
  Multi-row animated marquee background using trending movie posters and backdrops fetched from the TMDB API.

- **Hero Section**  
  Automatically highlights a featured trending title with a high-resolution backdrop, title, and overview on the home and genre pages.

- **Live Search**  
  Global search bar for discovering titles, cast members, or genres across the entire TMDB database.

- **Genre Filtering**  
  Sidebar navigation allowing users to filter between **Movies** and **TV Shows** across various categories.

---

### 👤 User Experience
- **Secure Authentication**  
  User registration and login using `password_hash()` and `password_verify()` for secure credential handling.

- **Personalized “My List”**  
  Users can star titles to save them. The UI updates instantly using AJAX (Fetch API) without page reloads.

- **Rich Detail Pages**
  - 🎞️ Embedded YouTube trailers and teasers  
  - 👥 Cast credits with photos and character names  
  - 🔁 “More Like This” recommendations

---

### 💻 Technical Implementation
- **Sticky Navigation**  
  Header transitions from transparent to solid black on scroll.

- **Responsive Layout**  
  CSS Grid & Flexbox layout that adapts from desktop sidebar navigation to a mobile-friendly horizontal genre bar.

- **State Management**  
  PHP sessions protect routes and ensure only authenticated users can access the dashboard.

---

## 🛠️ Technology Stack

**Frontend**
- HTML5  
- CSS3 (Custom Properties, Keyframe Animations)  
- JavaScript (Vanilla ES6, Fetch API)

**Backend**
- PHP 8.x  
- MySQL

**API**
- The Movie Database (TMDB) API

---

## 📂 Project Structure

| File / Folder | Description |
|---------------|------------|
| `landing.php` | Entry point for guests with cinematic background animations |
| `index.php` | Main dashboard with Hero section, My List, and content rows |
| `detail.php` | Detailed title view with trailers, cast, and metadata |
| `js/main.js` | Sticky header logic and AJAX star toggling |
| `css/style.css` | Complete styling for layout, theme, and responsiveness |
| `db_connect.php` | MySQL connection and session initialization |
| `api/toggle_star.php` | API endpoint for adding/removing starred titles |
| `login.php` / `register.php` | Secure user authentication and session creation |

---

## ⚙️ Installation & Setup

### 1️⃣ Database Configuration

Create a MySQL database named `netflixdb` and execute the following SQL:

```sql
/* Users Table */
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* Starred Content Table */
CREATE TABLE user_stars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    tmdb_id INT,
    type VARCHAR(10),
    title VARCHAR(255),
    poster_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 2️⃣ TMDB API KEY SETUP

1. Create an account at https://www.themoviedb.org
2. Go to Account Settings → API
3. Generate an API key
4. Insert the key into:
   - tmdb_api.php
   - get_details.php

### 3️⃣ SERVER CONFIGURATION

Recommended local environments:
- XAMPP
- WAMP
- Laragon

Requirements:
- PHP 8.x
- MySQL
- Apache or Nginx

Steps:
1. Place the project folder in:
   - htdocs/ (XAMPP)
   - www/ (WAMP / Laragon)
2. Start Apache and MySQL
3. Open your browser and navigate to:
   http://localhost/myflix

--------------------------------------------------

📌 NOTES
- All movie and TV metadata is fetched dynamically from TMDB.
- User authentication and starred content are stored locally.
- UI inspired by modern streaming platforms.
