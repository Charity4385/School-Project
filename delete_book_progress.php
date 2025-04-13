<?php
// delete_book_progress.php

// Database Connection (Replace with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "progress_tracking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];

    // Delete all readings for the given book_id
    $sql = "DELETE FROM readings WHERE book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        echo "Progress deleted successfully!";
    } else {
        echo "Error deleting progress: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request!";
}

$conn->close();
?>