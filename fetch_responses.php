<?php
include "config.php";

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");

// Check database connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Validate and sanitize input
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid post_id parameter"]);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("SELECT * FROM responses WHERE post_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to prepare statement: " . $conn->error]);
    exit;
}

// Bind parameters and execute the statement
$stmt->bind_param("i", $post_id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to execute statement: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

// Fetch results
$result = $stmt->get_result();
$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[] = $row;
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return JSON response
http_response_code(200);
echo json_encode(["success" => true, "data" => $responses]);
?>
