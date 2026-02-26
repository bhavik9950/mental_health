<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config.php';

// Enable CORS for all origins
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request (CORS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from $_POST
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $query = isset($_POST['query']) ? $_POST['query'] : null;

    // Check if data was received
    if (!$name || !$email || !$query) {
        die(json_encode(['status' => 'error', 'message' => 'No form data received']));
    }

    // Database connection
    $conn = getDBConnection();

    if (!$conn) {
        die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
    }

    // Prepare the SQL query
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, query) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $query);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Form submitted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save data to the database: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
