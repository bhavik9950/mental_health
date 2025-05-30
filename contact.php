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
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $query = $_POST['query'] ?? null;

    if (!$name || !$email || !$query) {
        echo json_encode(['status' => 'error', 'message' => 'No form data received']);
        exit;
    }

    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contacts (name, email, qu) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $query);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Contact form submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
