<?php
require_once 'db_connect.php';
require_once 'tmdb_api.php'; 

// --- CONTENT PROTECTION CHECK ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: landing.php");
    exit;
}
// ---------------------------------

// 1. Fetch User's Starred Movies from Database
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT tmdb_id, type, title, poster_path FROM user_stars WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$starred_result = $stmt->get_result();

$starred_movies = [];
$starred_ids = [];
while ($row = $starred_result->fetch_assoc()) {
    $starred_movies[] = $row;
    $starred_ids[] = $row['tmdb_id'];
}
$stmt->close();

// 2. Determine Content Type and View Mode
$type_param = isset($_GET['type']) ? $_GET['type'] : null;
$type = ($type_param === 'tv') ? 'tv' : 'movie';

// 3. Check for SEARCH Query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// 4. Initialize Variables
$hero_movie = null;
$all_rows = [];
$selected_genre_id = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : 0;
$selected_genre_name = 'Trending Now';

// Detect if we are on the "True Homepage"
$is_homepage = empty($search_query) && $type_param === null && $selected_genre_id === 0;

// --- SEARCH MODE vs BROWSE MODE ---
if (!empty($search_query)) {
    // SEARCH MODE (No Hero)
    $encoded_query = urlencode($search_query);
    $all_rows = [
        "Results for: " . htmlspecialchars($search_query) => "search/multi?query={$encoded_query}&include_adult=false"
    ];
    $selected_genre_id = -1; 
    $all_genres = []; 
} else {
    // BROWSE MODE
    
    // Fetch genres for sidebar
    $genre_url = "https://api.themoviedb.org/3/genre/{$type}/list?api_key={$api_key}&language=en-US";
    $genre_json = @file_get_contents($genre_url);
    $genre_data = json_decode($genre_json, true);
    $all_genres = $genre_data['genres'] ?? [];

    if ($is_homepage) {
        // === TRUE HOMEPAGE (Mixed Content) ===
        $hero_url = "https://api.themoviedb.org/3/trending/all/day?api_key={$api_key}&language=en-US";
        
        $all_rows = [
            "Trending Movies" => "trending/movie/week",
            "Trending TV Shows" => "trending/tv/week",
            "Popular Movies" => "movie/popular",
            "Popular TV Shows" => "tv/popular"
        ];
    } else {
        // === SPECIFIC TYPE OR GENRE PAGE ===
        
        if ($selected_genre_id > 0) {
            // GENRE SELECTED
            $genre_key = array_search($selected_genre_id, array_column($all_genres, 'id'));
            if ($genre_key !== false) {
                $selected_genre_name = $all_genres[$genre_key]['name'];
            }

            // Define Rows for Genre
            $main_endpoint = "discover/{$type}?with_genres={$selected_genre_id}&sort_by=popularity.desc"; 
            $popular_endpoint = "discover/{$type}?with_genres={$selected_genre_id}&sort_by=revenue.desc"; 
            $top_rated_endpoint = "discover/{$type}?with_genres={$selected_genre_id}&sort_by=vote_average.desc&vote_count.gte=200";
            $main_title = "Trending {$selected_genre_name}";

            // *** NEW HERO LOGIC FOR GENRE ***
            // Fetch a popular movie from this specific genre to be the hero
            $hero_url = "https://api.themoviedb.org/3/discover/{$type}?api_key={$api_key}&with_genres={$selected_genre_id}&sort_by=popularity.desc&page=1";

        } else {
            // DEFAULT TYPE LANDING (e.g., Just "Movies" or "TV Shows")
            $main_endpoint = "trending/{$type}/week"; 
            $popular_endpoint = "{$type}/popular";
            $top_rated_endpoint = "{$type}/top_rated";
            $main_title = "Trending Now";

            // Hero: Trending for specific type
            $hero_url = "https://api.themoviedb.org/3/trending/{$type}/day?api_key={$api_key}&language=en-US";
        }

        $type_label = $type === 'tv' ? 'TV Shows' : 'Movies';
        $popular_title = $selected_genre_id > 0 ? "Popular {$selected_genre_name} {$type_label}" : "Popular {$type_label}";
        $top_rated_title = $selected_genre_id > 0 ? "Highly Rated {$selected_genre_name} {$type_label}" : "Highly Rated {$type_label}";

        $all_rows = [
            $main_title => $main_endpoint, 
            $popular_title => $popular_endpoint,
            $top_rated_title => $top_rated_endpoint,
        ];
    }

    // FETCH HERO MOVIE DATA
    // We perform the fetch here based on whatever $hero_url was set above
    if (isset($hero_url)) {
        $hero_data = json_decode(@file_get_contents($hero_url), true);
        if (!empty($hero_data['results'])) {
            // Pick the first one (most popular/trending)
            $hero_movie = $hero_data['results'][0];
            
            // Optional: Pick a random one from the top 5 to make it feel dynamic on refresh
            // $random_index = array_rand(array_slice($hero_data['results'], 0, 5));
            // $hero_movie = $hero_data['results'][$random_index];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MYFLIX - <?php echo !empty($search_query) ? 'Search' : 'Home'; ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Inline styles for Hero Section to utilize PHP variables easily */
        .hero-section {
            height: 80vh;
            background-size: cover;
            background-position: center center;
            position: relative;
            color: white;
            margin-bottom: -100px; /* Pull rows up slightly */
            display: flex;
            align-items: center; /* Center content vertically */
        }
        /* Gradient Overlay */
        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #141414 0%, transparent 50%),
                        linear-gradient(to top, #141414 0%, transparent 50%);
        }
        .hero-content {
            position: relative;
            z-index: 10;
            padding-left: 60px;
            width: 40%;
            margin-top: 60px;
        }
        .hero-title {
            font-size: 4em;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero-overview {
            font-size: 1.2em;
            margin-bottom: 20px;
            line-height: 1.4;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            /* Clamp text to 3 lines */
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .hero-buttons button {
            padding: 12px 25px;
            font-size: 1.1em;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            transition: opacity 0.2s;
        }
        .hero-buttons button:hover { opacity: 0.8; }
        .btn-play { background-color: white; color: black; }
        .btn-info { background-color: rgba(109, 109, 110, 0.7); color: white; }
        .btn-play i, .btn-info i { margin-right: 8px; }

        /* Adjust layout when sidebar is present */
        .main-layout-container { padding-top: 0; }
        .sidebar { top: 0; height: 100vh; padding-top: 80px; } /* Make sidebar span full height */
        
        @media (max-width: 900px) {
            .hero-section { height: 60vh; margin-bottom: 0; align-items: flex-end; padding-bottom: 40px; }
            .hero-content { width: 100%; padding-left: 20px; padding-right: 20px; }
            .hero-title { font-size: 2.5em; }
            .hero-overview { display: none; /* Hide text on mobile */ }
            .sidebar { padding-top: 20px; height: auto; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <div class="logo">
                <i class="fas fa-film"></i> MYFLIX
            </div>
            <nav class="nav-links">
                <a href="index.php" style="<?php echo $is_homepage ? 'font-weight:bold; color:white;' : ''; ?>">Home</a>
                <a href="index.php?type=tv" style="<?php echo $type_param === 'tv' ? 'font-weight:bold; color:white;' : ''; ?>">TV Shows</a>
                <a href="index.php?type=movie" style="<?php echo $type_param === 'movie' ? 'font-weight:bold; color:white;' : ''; ?>">Movies</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
        
        <!-- NEW: Welcome Message in Center -->
        <div class="header-center" style="position: absolute; left: 50%; transform: translateX(-50%); color: white; font-size: 1.1em;">
            Welcome <?php echo htmlspecialchars($_SESSION['username']); ?> !
        </div>
        
        <div class="header-right">
            <form action="index.php" method="GET" class="search-form">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Titles, people, genres" value="<?php echo htmlspecialchars($search_query); ?>">
            </form>
        </div>
    </header>

    <div class="main-layout-container">

        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>Genres</h2>
            <a href="index.php?type=<?php echo $type; ?>" class="genre-link <?php echo $selected_genre_id == 0 ? 'active' : ''; ?>">All</a>
            
            <?php foreach ($all_genres as $genre): ?>
                <a href="index.php?type=<?php echo $type; ?>&genre_id=<?php echo $genre['id']; ?>" 
                   class="genre-link <?php echo $selected_genre_id == $genre['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($genre['name']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Main Content Area -->
        <div class="content-area">
            
            <!-- NEW: HERO SECTION (Only visible when not searching) -->
            <?php if (empty($search_query) && $hero_movie): 
                $hero_backdrop = $image_base_url . $hero_movie['backdrop_path']; // High res
                // Handle Title vs Name (Movie vs TV)
                $hero_title = isset($hero_movie['title']) ? $hero_movie['title'] : (isset($hero_movie['name']) ? $hero_movie['name'] : 'Featured Title');
                $hero_type = isset($hero_movie['media_type']) ? $hero_movie['media_type'] : $type;
            ?>
            <div class="hero-section" style="background-image: url('<?php echo $hero_backdrop; ?>');">
                <div class="hero-content">
                    <div class="hero-title"><?php echo htmlspecialchars($hero_title); ?></div>
                    <div class="hero-overview"><?php echo htmlspecialchars($hero_movie['overview']); ?></div>
                    <div class="hero-buttons">
                        <button class="btn-play" onclick="location.href='detail.php?type=<?php echo $hero_type; ?>&id=<?php echo $hero_movie['id']; ?>'">
                            <i class="fas fa-play"></i> Play
                        </button>
                        <button class="btn-info" onclick="location.href='detail.php?type=<?php echo $hero_type; ?>&id=<?php echo $hero_movie['id']; ?>'">
                            <i class="fas fa-info-circle"></i> More Info
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- "My List" Row -->
            <?php if (empty($search_query) && !empty($starred_movies)): ?>
            <div class="movie-row-container" style="position: relative; z-index: 10;">
                <h2 class="row-title" style="color: #ffd700;">My List (Starred)</h2>
                <div class="movie-row">
                    <?php foreach ($starred_movies as $movie): 
                        $poster_url = $image_base_url . $movie['poster_path'];
                    ?>
                        <a href="detail.php?type=<?php echo $movie['type']; ?>&id=<?php echo $movie['tmdb_id']; ?>">
                            <div class="movie-card">
                                <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                <div class="star-btn active" onclick="toggleStar(event, <?php echo $movie['tmdb_id']; ?>, '<?php echo $movie['type']; ?>', '<?php echo addslashes($movie['title']); ?>', '<?php echo $movie['poster_path']; ?>')">★</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Content Rows -->
            <?php foreach ($all_rows as $title => $endpoint): 
                $separator = (strpos($endpoint, '?') === false) ? '?' : '&';
                $url = "https://api.themoviedb.org/3/{$endpoint}{$separator}api_key={$api_key}&language=en-US&page=1";
                $json_data = @file_get_contents($url); 
                $movies = json_decode($json_data, true);
            ?>

            <div class="movie-row-container" style="position: relative; z-index: 10;">
                <h2 class="row-title"><?php echo $title; ?></h2>
                <div class="movie-row">
                    <?php 
                    if (isset($movies['results']) && is_array($movies['results'])):
                        $count = 0;
                        foreach ($movies['results'] as $item): 
                            if (isset($item['media_type']) && $item['media_type'] == 'person') continue;
                            if ($count >= 20) break; 
                            
                            $poster_path = $item['poster_path'] ?? null;
                            if (empty($poster_path)) continue; 

                            $item_title = isset($item['title']) ? $item['title'] : (isset($item['name']) ? $item['name'] : 'Unknown');
                            $poster_url = $image_base_url . $poster_path;
                            $is_starred = in_array($item['id'], $starred_ids);
                            $current_item_type = isset($item['media_type']) ? $item['media_type'] : $type;
                    ?>
                        <a href="detail.php?type=<?php echo $current_item_type; ?>&id=<?php echo $item['id']; ?>">
                            <div class="movie-card">
                                <img src="<?php echo $poster_url; ?>" alt="<?php echo htmlspecialchars($item_title); ?>">
                                
                                <div class="star-btn <?php echo $is_starred ? 'active' : ''; ?>" 
                                     onclick="toggleStar(event, <?php echo $item['id']; ?>, '<?php echo $current_item_type; ?>', '<?php echo addslashes($item_title); ?>', '<?php echo $poster_path; ?>')">
                                    <?php echo $is_starred ? '★' : '☆'; ?>
                                </div>
                            </div>
                        </a>
                    <?php 
                        $count++; 
                        endforeach; 
                    endif; 
                    ?>
                </div>
            </div>

            <?php endforeach; ?>
        </div>
    </div> 

    <script src="js/main.js"></script>
</body>
</html>