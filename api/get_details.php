<?php
// Ensure this file is accessible to AJAX calls
header('Content-Type: application/json');

// Include API key from your tmdb_api.php file
// Note: If tmdb_api.php starts with ob_start(), remove it or adjust paths.
// For simplicity, we'll redefine the key here for this endpoint:
$api_key = "API_KEY"; // Use your actual key
$image_base_url = "https://image.tmdb.org/t/p/w500"; 

$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movie_id > 0) {
    // 1. Fetch detailed movie data from TMDB
    $url = "https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US";
    $json_data = @file_get_contents($url);
    $movie_details = json_decode($json_data, true);

    if ($movie_details) {
        // 2. Format the essential data
        $data = [
            'success' => true,
            'id' => $movie_details['id'],
            'title' => htmlspecialchars($movie_details['title']),
            'overview' => htmlspecialchars($movie_details['overview']),
            'release_date' => date('Y', strtotime($movie_details['release_date'])),
            'runtime' => $movie_details['runtime'],
            'rating' => round($movie_details['vote_average'], 1),
            'genres' => array_column($movie_details['genres'], 'name'),
            'backdrop_path' => $image_base_url . $movie_details['backdrop_path']
        ];
        echo json_encode($data);
    } else {
        echo json_encode(['success' => false, 'message' => 'Movie not found or API error.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Movie ID.']);
}
?>