<?php
require_once '../db_connect.php'; // Adjust path to point to your root db_connect.php

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['tmdb_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$user_id = $_SESSION['id'];
$tmdb_id = $input['tmdb_id'];
$type = $input['type'] ?? 'movie';
$title = $input['title'] ?? '';
$poster_path = $input['poster_path'] ?? '';

// Check if the movie is already starred
$stmt = $conn->prepare("SELECT id FROM user_stars WHERE user_id = ? AND tmdb_id = ? AND type = ?");
$stmt->bind_param("iis", $user_id, $tmdb_id, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // It exists, so REMOVE it (Unstar)
    $stmt = $conn->prepare("DELETE FROM user_stars WHERE user_id = ? AND tmdb_id = ? AND type = ?");
    $stmt->bind_param("iis", $user_id, $tmdb_id, $type);
    $stmt->execute();
    $action = 'removed';
} else {
    // It doesn't exist, so ADD it (Star)
    $stmt = $conn->prepare("INSERT INTO user_stars (user_id, tmdb_id, type, title, poster_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $tmdb_id, $type, $title, $poster_path);
    $stmt->execute();
    $action = 'added';
}

echo json_encode(['success' => true, 'action' => $action]);