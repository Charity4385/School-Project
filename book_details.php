<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles3.css">
</head>
<body>
    <header>
    <h1>Welcome to book lovers</h1>
        <nav>
            <ul>
            <li><a href="index.html">Home</a></li>
                <li><a href="book_details.php">Book-Details</a></li>
                <li><a href="progress_tracking.php">Progress-Tracking</a></li>
                <li><a href="reading_progress.php">Reading-progress</a></li>
                <li><a href="reading_goals.php">Reading Goals</a></li>
                
            </ul>
        </nav>
    </header>

    <main>
        <form id="add-book-form" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="author">Author:</label>
                <input type="text" id="author" name="author" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="total_pages">Total Pages:</label>
                <input type="number" id="total_pages" name="total_pages" required min="1">
            </div>
            <div class="form-group">
                <label for="cover_image">Cover Image:</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
            </div>
            <button type="submit" class="button primary-button">Add Book</button>
        </form>

        <div id="message" class="message">
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
                die("Connection failed: " . $conn->connect_error);
            }

            // Function to display books with edit and delete options
            function displayBooks($conn) {
                $sql = "SELECT * FROM books";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<div class='book-list'>";
                    echo "<h3>List of Books</h3>";
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='book-item'>";
                        if ($row["cover_image"]) {
                            echo "<img src='" . $row["cover_image"] . "' alt='Cover Image'>";
                        }
                        echo "<div class='book-item-details'>";
                            echo "<h4>Title: " . $row["title"] . "</h4>";
                            echo "<p>Author: " . $row["author"] . "</p>";
                            echo "<p>Start Date: " . $row["start_date"] . "</p>";
                            echo "<p>End Date: " . $row["end_date"] . "</p>";
                            echo "<p>Total Pages: " . $row["total_pages"] . "</p>";
                            echo "<div class='actions'>";
                                echo "<button class='edit-button' onclick='editBook(" . $row["id"] . ")'>Edit</button>";
                                echo "<button class='delete-button' onclick='deleteBook(" . $row["id"] . ")'>Delete</button>";
                            echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No books found in the database.</p>";
                }
            }

            // Check if the form has been submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // 2. Form Data Processing
                $title = mysqli_real_escape_string($conn, $_POST["title"]);
                $author = mysqli_real_escape_string($conn, $_POST["author"]);
                $start_date = $_POST["start_date"];
                $end_date = $_POST["end_date"];
                $total_pages = intval($_POST["total_pages"]);

                // Basic validation
                if (empty($title) || empty($author) || empty($start_date) || empty($end_date) || $total_pages <= 0) {
                    echo "<p class='error'>Please fill in all the fields with valid information.</p>";
                } else {

                    // 3. File Upload Handling
                    $uploadDir = "uploads/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $uploadFile = $uploadDir . basename($_FILES["cover_image"]["name"]);
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

                    // Check if image file is a actual image or fake image
                    $check = getimagesize($_FILES["cover_image"]["tmp_name"]);
                    if($check !== false) {
                        $uploadOk = 1;
                    } else {
                        $uploadOk = 0;
                        echo "<p class='error'>File is not an image.</p>";
                    }

                    // Check if file already exists
                    if (file_exists($uploadFile)) {
                        $uploadOk = 0;
                        echo "<p class='error'>Sorry, file already exists.</p>";
                    }

                    // Check file size
                    if ($_FILES["cover_image"]["size"] > 5000000) {
                        $uploadOk = 0;
                        echo "<p class='error'>Sorry, your file is too large.</p>";
                    }

                    // Allow certain file formats
                    $allowedFormats = array("jpg", "png", "jpeg", "gif");
                    if(!in_array($imageFileType, $allowedFormats)) {
                        $uploadOk = 0;
                        echo "<p class='error'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
                    }

                    if ($uploadOk == 0) {
                         echo "<p class='error'>File upload failed.</p>";
                    } else {
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $uploadFile)) {
                            // File uploaded successfully
                            $cover_image_url = $uploadFile;
                            // 4. Database Insertion
                            $sql = "INSERT INTO books (title, author, start_date, end_date, total_pages, cover_image) VALUES ('$title', '$author', '$start_date', '$end_date', $total_pages, '$cover_image_url')";

                            if ($conn->query($sql) === TRUE) {
                                echo "<p class='success'>Book added successfully!</p>";
                                echo "<script>
                                        document.getElementById('title').value = '';
                                        document.getElementById('author').value = '';
                                        document.getElementById('start_date').value = '';
                                        document.getElementById('end_date').value = '';
                                        document.getElementById('total_pages').value = '';
                                        document.getElementById('cover_image').value = '';
                                        </script>";
                            } else {
                                echo "<p class='error'>Error adding book: " . $conn->error . "</p>";
                            }
                        } else {
                            echo "<p class='error'>Sorry, there was an error uploading your file.</p>";
                        }
                    }
                }
            }

            // Display the books
            displayBooks($conn);
            ?>
        </div>
    </main>

    <script>
        function editBook(id) {
            
            window.location.href = "update_book.php?id=" + id;
        }

        function deleteBook(id) {
       if (confirm('Are you sure you want to delete this book?')) {
           var xhr = new XMLHttpRequest();
           xhr.open("POST", "delete_book.php", true);
           xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
           xhr.onload = function () {
               if (xhr.status == 200) {
                   try {
                       var response = JSON.parse(xhr.responseText);
                       if (response.success) {
                           // Book deleted successfully, reload the page
                           window.location.reload(); 
                       } else {
                           alert('Error deleting book: ' + response.message); 
                       }
                   } catch (e) {
                       alert('Error parsing server response.');
                   }
               } else {
                   alert('Error deleting book (HTTP status ' + xhr.status + ').');
               }
           };
           xhr.send("id=" + id); // Send the book ID to the server
       }
   }
    </script>
    <footer>
        <p>&copy; 2025 Book Lovers. All rights reserved.</p>
    </footer>
</body>
</html>
