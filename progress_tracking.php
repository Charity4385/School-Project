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
    <link rel="stylesheet" href="styles4.css" >
</head>

<body>
    <header>
        <h1>Welcome to book lovers</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="book_details.php">Book-Details</a></li>
                <li><a href="progress_tracking.php">Progress-Tracking</a></li>
                <li><a href="reading_progress.php">Reading-history Chart</a></li>
                <li><a href="reading_goals.php">Reading Goals</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2>Track your Progress</h2>

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

    <div class="container">
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