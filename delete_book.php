<?php
// 1. Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "progress_tracking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(array("success" => false, "message" => "Connection failed: " . $conn->connect_error));
    exit(); // Important: Stop further execution
}

// Check if the book ID is set in the URL
if (isset($_POST['id'])) { 
    $id = $_POST['id']; 

    // Prepare the SQL query to delete the book
    $sql = "DELETE FROM books WHERE id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind the book ID to the statement
    $stmt->bind_param("i", $id);

    // Execute the statement
    if ($stmt->execute()) {
        // If successful, send a JSON response indicating success
        echo json_encode(array("success" => true, "message" => "Book deleted successfully."));
    } else {
        // If there's an error, send a JSON response with the error message
        echo json_encode(array("success" => false, "message" => "Error deleting book: " . $stmt->error));
    }

    // Close the statement
    $stmt->close();
} else {
    // If no book ID is provided, send an error response
    echo json_encode(array("success" => false, "message" => "Book ID not provided."));
}

// Close the database connection
$conn->close();
?>