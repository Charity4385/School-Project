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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reading_id = $_POST['reading_id'];

    $sql = "DELETE FROM readings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reading_id);

    if ($stmt->execute()) {
        echo "Reading entry deleted successfully";
    } else {
        echo "Error deleting reading entry: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request method";
}

$conn->close();
?>