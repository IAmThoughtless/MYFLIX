<?php
// Include TMDB API to fetch trending movies for the background
// We don't need db_connect.php here as this page is for unauthenticated users
require_once 'tmdb_api.php'; 

// Fetch trending movies
$trending_url = "https://api.themoviedb.org/3/trending/movie/week?api_key={$api_key}&language=en-US";
$trending_data = json_decode(@file_get_contents($trending_url), true);
$trending_movies = $trending_data['results'] ?? [];

// Helper to duplicate movies to fill the screen width for animation
$bg_movies = array_merge($trending_movies, $trending_movies); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYFLIX - Watch TV Shows Online, Watch Movies Online</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="landing-body">
    
    <!-- 1. Centered Background Animation -->
    <div class="landing-background-animation">
        <div class="overlay-gradient"></div>
        
        <!-- Row 1 -->
        <div class="marquee-row scroll-left">
            <div class="marquee-content">
                <?php foreach ($bg_movies as $movie): $poster = $image_base_url . $movie['poster_path']; ?>
                    <img src="<?php echo $poster; ?>" alt="Poster">
                <?php endforeach; ?>
            </div>
            <div class="marquee-content">
                <?php foreach ($bg_movies as $movie): $poster = $image_base_url . $movie['poster_path']; ?>
                    <img src="<?php echo $poster; ?>" alt="Poster">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="marquee-row scroll-right">
            <div class="marquee-content">
                <?php foreach ($bg_movies as $movie): $poster = $image_base_url . $movie['backdrop_path']; ?>
                    <img src="<?php echo $poster; ?>" class="backdrop-img" alt="Backdrop">
                <?php endforeach; ?>
            </div>
            <div class="marquee-content">
                <?php foreach ($bg_movies as $movie): $poster = $image_base_url . $movie['backdrop_path']; ?>
                    <img src="<?php echo $poster; ?>" class="backdrop-img" alt="Backdrop">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="marquee-row scroll-left">
            <div class="marquee-content">
                <?php foreach (array_reverse($bg_movies) as $movie): $poster = $image_base_url . $movie['poster_path']; ?>
                    <img src="<?php echo $poster; ?>" alt="Poster">
                <?php endforeach; ?>
            </div>
            <div class="marquee-content">
                <?php foreach (array_reverse($bg_movies) as $movie): $poster = $image_base_url . $movie['poster_path']; ?>
                    <img src="<?php echo $poster; ?>" alt="Poster">
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 2. Header (Fixed to top) -->
    <header class="landing-header">
        <div class="logo">
            <i class="fas fa-film"></i> MYFLIX
        </div>
        <a href="login.php" class="btn-signin">Sign In</a>
    </header>

    <!-- 3. Centered Content Box -->
    <div class="landing-content-wrapper">
        <div class="landing-content-card">
            <h1>Unlimited movies, TV shows, and more.</h1>
            <h2>Watch anywhere. Cancel anytime.</h2>
            <p>Ready to watch? Create an account!</p>
            
            <form action="register.php" class="cta-form">
                <div class="email-input-container">
                    <!-- Input removed as requested -->
                    <button type="submit" class="btn-get-started">
                        Get Started <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>