<?php
include "config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: http://localhost:3000"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    $post_id = isset($data['post_id']) ? $data['post_id'] : null;
    $content = isset($data['content']) ? $data['content'] : null;

    if (!$post_id || !$content) {
        echo json_encode(['status' => 'error', 'message' => 'Missing post_id or content']);
        exit;
    }

    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO responses (post_id, content) VALUES (?, ?)"); 
    $stmt->bind_param("is", $post_id, $content); 

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Response submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save data to the database: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
