<?php
include "config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS for all origins
header("Access-Control-Allow-Origin: http://localhost:3000"); // React app origin
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $content = isset($_POST['content']) ? $_POST['content'] : null;

    if (!$name || !$content) {
        echo json_encode(['status' => 'error', 'message' => 'No form data received']);
        exit;
    }


    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO posts (name, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $content); 
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Post submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save data to the database: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
