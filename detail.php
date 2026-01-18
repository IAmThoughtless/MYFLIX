<?php
require_once 'db_connect.php'; 
require_once 'tmdb_api.php'; 

// --- CONTENT PROTECTION CHECK ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}
// ---------------------------------

// Get ID and Type (default to movie if missing)
$item_id = isset($_GET['id']) ? $_GET['id'] : null;
$type = isset($_GET['type']) ? $_GET['type'] : 'movie'; 

$youtube_key = null;
$details = [];
$cast = [];
$recommendations = [];

if ($item_id) {
    // 1. Fetch MAIN details
    $details_url = "https://api.themoviedb.org/3/{$type}/{$item_id}?api_key={$api_key}&language=en-US";
    $details = json_decode(@file_get_contents($details_url), true);

    // 2. Fetch VIDEOS (Trailer)
    $videos_url = "https://api.themoviedb.org/3/{$type}/{$item_id}/videos?api_key={$api_key}&language=en-US";
    $videos_data = json_decode(@file_get_contents($videos_url), true);

    // Find trailer key
    if (isset($videos_data['results'])) {
        foreach ($videos_data['results'] as $video) {
            if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                $youtube_key = $video['key'];
                break;
            }
        }
        if (!$youtube_key && !empty($videos_data['results'])) {
             $youtube_key = $videos_data['results'][0]['key'];
        }
    }

    // 3. Fetch CAST (Credits)
    $credits_url = "https://api.themoviedb.org/3/{$type}/{$item_id}/credits?api_key={$api_key}&language=en-US";
    $credits_data = json_decode(@file_get_contents($credits_url), true);
    $cast = array_slice($credits_data['cast'] ?? [], 0, 10); // Get top 10 actors

    // 4. Fetch RECOMMENDATIONS (Similar content)
    $rec_url = "https://api.themoviedb.org/3/{$type}/{$item_id}/recommendations?api_key={$api_key}&language=en-US&page=1";
    $rec_data = json_decode(@file_get_contents($rec_url), true);
    $recommendations = array_slice($rec_data['results'] ?? [], 0, 10);
}

// Handle Title/Name difference between Movie/TV
$title = isset($details['title']) ? $details['title'] : (isset($details['name']) ? $details['name'] : 'Details');
$backdrop_path = $details['backdrop_path'] ?? null;
$backdrop_url = $backdrop_path ? $image_base_url . $backdrop_path : ''; 
$release_date = isset($details['release_date']) ? $details['release_date'] : (isset($details['first_air_date']) ? $details['first_air_date'] : '');
$runtime = isset($details['runtime']) ? $details['runtime'] . ' min' : (isset($details['episode_run_time'][0]) ? $details['episode_run_time'][0] . ' min' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-left">
            <div class="logo">MYFLIX</div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="index.php?type=tv">TV Shows</a>
                <a href="index.php?type=movie">Movies</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="detail-header" style="background-image: url('<?php echo htmlspecialchars($backdrop_url); ?>');">
        <div class="info-box">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            
            <?php if (isset($details['tagline']) && !empty($details['tagline'])): ?>
                <h3 class="tagline">"<?php echo htmlspecialchars($details['tagline']); ?>"</h3>
            <?php endif; ?>

            <p class="meta">
                <?php echo isset($details['vote_average']) ? '★ ' . round($details['vote_average'], 1) . '/10' : ''; ?> | 
                <?php echo !empty($release_date) ? date('Y', strtotime($release_date)) : ''; ?> | 
                <?php echo $runtime; ?> |
                <?php echo isset($details['genres']) ? implode(', ', array_column($details['genres'], 'name')) : ''; ?>
            </p>
            <p class="overview"><?php echo htmlspecialchars($details['overview'] ?? 'No overview available.'); ?></p>
            
            <?php if ($youtube_key): ?>
                <button onclick="document.getElementById('trailer-section').scrollIntoView({behavior: 'smooth'});">
                    <i class="fas fa-play"></i> Watch Trailer
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-content-wrapper">
        
        <!-- 1. Top Cast Section -->
        <?php if (!empty($cast)): ?>
        <div class="section-container">
            <h2>Top Cast</h2>
            <div class="cast-row">
                <?php foreach ($cast as $actor): 
                    if (empty($actor['profile_path'])) continue;
                    $actor_img = $image_base_url . $actor['profile_path'];
                ?>
                <div class="cast-card">
                    <img src="<?php echo $actor_img; ?>" alt="<?php echo htmlspecialchars($actor['name']); ?>">
                    <p class="actor-name"><?php echo htmlspecialchars($actor['name']); ?></p>
                    <p class="character-name"><?php echo htmlspecialchars($actor['character']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 2. Trailer Section -->
        <div id="trailer-section" class="section-container">
            <h2>Trailer</h2>
            <?php if ($youtube_key): ?>
                <div class="trailer-embed">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        src="https://www.youtube.com/embed/<?php echo $youtube_key; ?>?rel=0&showinfo=0" 
                        frameborder="0" 
                        allow="encrypted-media" 
                        allowfullscreen>
                    </iframe>
                </div>
            <?php else: ?>
                <p>Trailer not available for this title.</p>
            <?php endif; ?>
        </div>

        <!-- 3. Recommendations Section -->
        <?php if (!empty($recommendations)): ?>
        <div class="section-container">
            <h2>More Like This</h2>
            <div class="movie-row">
                <?php foreach ($recommendations as $rec): 
                    if (empty($rec['poster_path'])) continue;
                    $rec_img = $image_base_url . $rec['poster_path'];
                    $rec_title = isset($rec['title']) ? $rec['title'] : (isset($rec['name']) ? $rec['name'] : '');
                ?>
                <a href="detail.php?type=<?php echo $type; ?>&id=<?php echo $rec['id']; ?>">
                    <div class="movie-card">
                        <img src="<?php echo $rec_img; ?>" alt="<?php echo htmlspecialchars($rec_title); ?>">
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>