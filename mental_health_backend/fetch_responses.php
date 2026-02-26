<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = getDBConnection();

if (!$conn) {
    die(json_encode(["error" => "Connection failed"]));
}


$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

$stmt = $conn->prepare("SELECT id, post_id, content, created_at FROM responses WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($responses);
?>
