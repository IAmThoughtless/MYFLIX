<?php
// Replace with your actual TMDB API key
$api_key = "API_KEY"; 

// Base URL for fetching high-resolution images
$image_base_url = "https://image.tmdb.org/t/p/w500"; 
function get_tmdb_genres($api_key) {
    $url = "https://api.themoviedb.org/3/genre/movie/list?api_key={$api_key}&language=en-US";
    // Suppress errors with @ to prevent page breakage if API fails
    $json_data = @file_get_contents($url);
    $data = json_decode($json_data, true);
    
    // Return genres array, or an empty array if fetch failed
    return $data['genres'] ?? [];
}
?>