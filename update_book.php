<?php
// 1. Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "progress_tracking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for the form
$id = "";
$title = "";
$author = "";
$start_date = "";
$end_date = "";
$total_pages = "";
$cover_image_url = "";
$is_edit = false; // Flag to indicate if it's an edit operation

// 2. Check if we're editing a book
if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        // Fetch the book data for editing
        $sql = "SELECT * FROM books WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $book = $result->fetch_assoc();
            $title = $book['title'];
            $author = $book['author'];
            $start_date = $book['start_date'];
            $end_date = $book['end_date'];
            $total_pages = $book['total_pages'];
            $cover_image_url = $book['cover_image'];
            $is_edit = true; // Set the flag to true
        } else {
            echo "Book not found."; // Handle the case where the book doesn't exist
            exit;
        }
        $stmt->close();
    } else {
         echo "Invalid Book ID";
         exit;
    }
}

// 3. Handle form submission (Add or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $total_pages = filter_input(INPUT_POST, 'total_pages', FILTER_VALIDATE_INT);
    $current_cover_image = filter_input(INPUT_POST, 'current_cover_image', FILTER_SANITIZE_URL);
    $coverImageURL = $current_cover_image;

     if ($total_pages === false) {
        $response = array("success" => false, "message" => "Invalid total pages.");
        echo json_encode($response);
        $conn->close();
        return;
    }


    // Handle file upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $uploadDir = 'uploads/';
        $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $uniqueFileName = uniqid() . '.' . $fileExtension;
        $coverImageURL = $uploadDir . $uniqueFileName;
        $maxFileSize = 2 * 1024 * 1024;

        if ($_FILES['cover_image']['size'] > $maxFileSize) {
             $response = array("success" => false, "message" => "File too large (max 2MB).");
             echo json_encode($response);
             $conn->close();
             return;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            $response = array("success" => false, "message" => "Invalid file type (jpg, jpeg, png, gif allowed).");
            echo json_encode($response);
            $conn->close();
            return;
        }

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImageURL)) {
             if ($current_cover_image && file_exists($current_cover_image)) {
                unlink($current_cover_image);
             }
        } else {
            $response = array("success" => false, "message" => "File upload failed.");
            echo json_encode($response);
            $conn->close();
            return;
        }
    }


    // Perform the update or insert
    if ($is_edit) {
        // Update existing book
        $sql = "UPDATE books SET title = ?, author = ?, start_date = ?, end_date = ?, total_pages = ?, cover_image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisi", $title, $author, $start_date, $end_date, $total_pages, $coverImageURL, $id);
    } else {
        // Insert new book
        $sql = "INSERT INTO books (title, author, start_date, end_date, total_pages, cover_image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssis", $title, $author, $start_date, $end_date, $total_pages, $coverImageURL);
    }

    if ($stmt->execute()) {
        $message = $is_edit ? "Book updated successfully." : "Book added successfully.";
        echo "<script>alert('$message'); window.location.href='book_details.php';</script>"; // Redirect to index.php
    } else {
        $message = $is_edit ? "Error updating book: " : "Error adding book: ";
        echo "<script>alert('$message  . $stmt->error');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? "Edit Book" : "Add Book"; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa; /* Light background */
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #ffffff; /* White container */
            padding: 30px;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        h1 {
            color: #2c3e50; /* Dark heading color */
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }
        .form-group label {
            font-weight: 600;
            color: #34495e; /* Dark label color */
        }
        .btn-primary {
            background-color: #007bff; /* Blue button */
            border-color: #007bff;
            font-weight: 600;
            transition: all 0.3s ease; /* Smooth transition */
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #e9ecef; /* Light gray button */
            border-color: #e9ecef;
            color: #34495e; /* Dark text color */
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #d3d9df; /* Slightly darker gray on hover */
            border-color: #d3d9df;
        }
        .form-control:focus {
            border-color: #007bff; /* Blue focus border */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Blue focus shadow */
        }
        .error-message {
            color: #e74c3c; /* Red error message */
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .img-thumbnail {
            max-width: 100%;
            height: auto;
            border-radius: 8px; /* Rounded corners for image */
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Small shadow for image */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><?php echo $is_edit ? "Edit Book" : "Add Book"; ?></h1>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="current_cover_image" value="<?php echo htmlspecialchars($cover_image_url); ?>">

            <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="author">Author <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($author); ?>" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
            </div>
            <div class="form-group">
                <label for="total_pages">Total Pages <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="total_pages" name="total_pages" value="<?php echo htmlspecialchars($total_pages); ?>" required>
            </div>
            <div class="form-group">
                <label for="cover_image">Cover Image</label>
                <input type="file" class="form-control-file" id="cover_image" name="cover_image">
                <?php if ($cover_image_url): ?>
                    <img src="<?php echo htmlspecialchars($cover_image_url); ?>" alt="Current Cover" style="max-width: 100px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? "Update Book" : "Add Book"; ?></button>
            <a href="book_details.php" class="btn btn-secondary ml-2">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#start_date, #end_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });
    </script>
</body>
</html>
