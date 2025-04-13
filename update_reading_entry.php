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
    $page_number = $_POST['page_number'];
    $date = $_POST['date'];

    // Fetch book_id and total_pages for progress calculation
    $sql_book_info = "SELECT book_id, books.total_pages FROM readings JOIN books ON readings.book_id = books.id WHERE readings.id = ?";
    $stmt_book_info = $conn->prepare($sql_book_info);
    $stmt_book_info->bind_param("i", $reading_id);
    $stmt_book_info->execute();
    $result_book_info = $stmt_book_info->get_result();

    if ($result_book_info->num_rows > 0) {
        $row_book_info = $result_book_info->fetch_assoc();
        $book_id = $row_book_info['book_id'];
        $total_pages = $row_book_info['total_pages'];

        // Calculate progress percentage
        $progress = ($page_number / $total_pages) * 100;

        $sql = "UPDATE readings SET current_page = ?, date = ?, progress = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $page_number, $date, $progress, $reading_id);

        if ($stmt->execute()) {
            echo "Reading entry updated successfully";
        } else {
            echo "Error updating reading entry: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Reading entry not found!";
    }

} else {
    echo "Invalid request method";
}

$conn->close();
?>