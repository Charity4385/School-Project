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
$book_id = $_POST['book_id'];
$page_number = $_POST['page_number'];
$date = $_POST['date'];

$sql = "UPDATE readings SET current_page = ?, date = ? WHERE book_id = ? ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $page_number, $date, $book_id);

if ($stmt->execute()) {
    echo "Progress updated successfully!";
} else {
    echo "Error updating progress: " . $stmt->error;
}
$conn->close();
?>