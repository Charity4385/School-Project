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

// Handle Form Submission (Add Progress)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_progress'])) {
    $book_id = $_POST['book_id'];
    $page_number = $_POST['page_number'];
    $date = $_POST['date'];

    // Fetch book details (title, author, total pages)
    $sql_book_details = "SELECT title, author, total_pages FROM books WHERE id = ?";
    $stmt_book_details = $conn->prepare($sql_book_details);
    $stmt_book_details->bind_param("i", $book_id);
    $stmt_book_details->execute();
    $result_book_details = $stmt_book_details->get_result();

    if ($result_book_details->num_rows > 0) {
        $row_book_details = $result_book_details->fetch_assoc();
        $total_pages = $row_book_details['total_pages'];
        $book_title = $row_book_details['title'];
        $book_author = $row_book_details['author'];

        // Calculate progress percentage
        $progress = ($page_number / $total_pages) * 100;

        // Insert into readings table with title and author
        $sql = "INSERT INTO readings (book_id, current_page, date, progress, title, author) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisiss", $book_id, $page_number, $date, $progress, $book_title, $book_author);

        if ($stmt->execute()) {
            echo "<script>alert('Progress added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding progress: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Book not found!');</script>";
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_progress'])) {
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
            echo "<script>alert('Progress updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating progress: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Reading entry not found!');</script>";
    }
}

// Handle Delete Progress
if (isset($_GET['delete_progress'])) {
    $reading_id = $_GET['delete_progress'];
    $sql = "DELETE FROM readings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reading_id);

    if ($stmt->execute()) {
        echo "<script>alert('Progress deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting progress: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Fetch Books and Progress Data (Corrected)
$sql = "SELECT books.id, books.title, books.cover_image, books.total_pages, 
            (SELECT MAX(current_page) FROM readings WHERE readings.book_id = books.id) AS last_page_read 
        FROM books";
$result = $conn->query($sql);

$books = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

// Fetch Reading History for each book (Corrected)
function getReadingHistory($conn, $book_id) {
    $sql = "SELECT id, current_page, date FROM readings WHERE book_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    $stmt->close();
    return $history;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav li {
            display: inline-block;
            margin: 0 10px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        nav a:hover {
            background-color: #555;
        }

        .container {
            max-width: 1200px;
            margin-top: 30px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }

        .book-item {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
            position: relative; /* For positioning progress bar */
        }

        .book-item img {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .book-item h3 {
            color: #34495e;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .book-item p {
            color: #555;
            margin-bottom: 8px;
        }

        .book-item .btn {
            margin: 5px;
        }

        .reading-history {
            margin-top: 20px;
            text-align: left;
        }

        .reading-history h4 {
            color: #34495e;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .reading-history ul {
            list-style: none;
            padding: 0;
        }

        .reading-history li {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reading-history li:last-child {
            border-bottom: none;
        }

        .reading-history .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .form-group label {
            font-weight: 600;
            color: #34495e;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
        }

        /* Progress Bar Styles */
        .progress-container {
            width: 100%;
            height: 20px;
            background-color: #e0e0de;
            border-radius: 10px;
            margin-top: 10px;
        }

        .progress-bar {
            height: 20px;
            border-radius: 10px;
            background-color: #4caf50; /* Green color */
            width: 0%; /* Initial width */
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .book-item {
                padding: 15px;
            }

            .book-item img {
                max-width: 100px;
            }
        }

        .progress-percentage {
            margin-top: 5px;
            font-size: 0.9em;
            color: #555;
        }

        footer {
            position: relative;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #312f2f;
            text-align: center;
            padding: 20px;
            color: white;
        }
    </style>
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
    <div class="container">
        <h1>Progress Tracking</h1>

        <form method="post">
            <div class="form-group">
                <label for="book_id">Book:</label>
                <select class="form-control" name="book_id" id="book_id">
                    <?php foreach ($books as $book): ?>
                        <option value="<?php echo $book['id']; ?>"><?php echo $book['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="page_number">Page Number:</label>
                <input type="number" class="form-control" name="page_number" id="page_number">
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" class="form-control" name="date" id="date">
            </div>
            <button type="submit" class="btn btn-primary" name="add_progress">Add Progress</button>
        </form>

        <div class="row">
            <?php foreach ($books as $book): ?>
                <div class="col-md-6">
                    <div class="book-item">
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover" style="max-width: 150px;">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p>Total Pages: <?php echo htmlspecialchars($book['total_pages']); ?></p>
                        <p>Last Page Read: <?php echo htmlspecialchars($book['last_page_read'] ?? 'N/A'); ?></p>

                        <div class="progress-container">
                            <div class="progress-bar" id="progress-bar-<?php echo $book['id']; ?>" style="width: <?php
                                $progress = ($book['last_page_read'] / $book['total_pages']) * 100;
                                echo min(100, max(0, $progress));
                            ?>%;"></div>
                        </div>

                        <p class="progress-percentage">
                            <?php
                                $progress = ($book['last_page_read'] / $book['total_pages']) * 100;
                                echo round(min(100, max(0, $progress)), 2) . "%";
                            ?>
                        </p>

                        <button type="button" class="btn btn-warning" onclick="editBookProgress(<?php echo $book['id']; ?>)">Edit Progress</button>
                        <button type="button" class="btn btn-danger" onclick="deleteBookProgress(<?php echo $book['id']; ?>)">Delete Progress</button>

                        <div class="reading-history">
                            <h4>Reading History</h4>
                            <?php $history = getReadingHistory($conn, $book['id']); ?>
                            <?php if (!empty($history)): ?>
                                <ul>
                                    <?php foreach ($history as $entry): ?>
                                        <li>
                                            Page: <?php echo htmlspecialchars($entry['current_page']); ?> - Date: <?php echo htmlspecialchars($entry['date']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No reading history available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    
        </div>

    <div class="modal fade" id="editProgressModal" tabindex="-1" role="dialog" aria-labelledby="editProgressModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProgressModalLabel">Edit Progress</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProgressForm">
                        <input type="hidden" name="reading_id" id="edit_reading_id">
                        <input type="hidden" name="book_id" id="edit_book_id">
                        <div class="form-group">
                            <label for="edit_page_number">Page Number:</label>
                            <input type="number" class="form-control" name="page_number" id="edit_page_number">
                        </div>
                        <div class="form-group">
                            <label for="edit_date">Date:</label>
                            <input type="date" class="form-control" name="date" id="edit_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveProgressBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function editBookProgress(bookId) {
            $.ajax({
                url: 'get_book_progress.php',
                type: 'GET',
                data: { book_id: bookId },
                success: function (data) {
                    data = JSON.parse(data);
                    $('#edit_book_id').val(bookId);
                    $('#edit_page_number').val(data.current_page);
                    $('#edit_date').val(data.date);
                    $('#editProgressModal').modal('show');
                }
            });
        }

        function deleteBookProgress(bookId) {
            if (confirm("Are you sure you want to delete all progress for this book?")) {
                $.ajax({
                    url: 'delete_book_progress.php',
                    type: 'POST',
                    data: { book_id: bookId },
                    success: function (response) {
                        alert(response);
                        location.reload();
                    }
                });
            }
        }

        $(document).ready(function () {
            $('#saveProgressBtn').click(function () {
                $.ajax({
                    url: 'update_book_progress.php',
                    type: 'POST',
                    data: $('#editProgressForm').serialize(),
                    success: function (response) {
                        alert(response);
                        $('#editProgressModal').modal('hide');
                        location.reload();
                    }
                });
            });
        });

    </script>
    <footer>
        <p>&copy; 2025 Book Lovers. All rights reserved.</p>
    </footer>
</body>

</html>