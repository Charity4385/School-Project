<?php
// Database Connection (Replace with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "progress_tracking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['reading_id'])) {
    $reading_id = $_GET['reading_id'];

    $sql = "SELECT current_page, date FROM readings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reading_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Reading entry not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Reading ID not provided']);
}

$conn->close();
?>