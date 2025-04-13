<?php
// 1. Database Connection (Make sure to update with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "progress_tracking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$goal_id = "";
$goal_start_date = "";
$goal_end_date = "";
$goal_books_to_read = "";
$goal_status = "";
$is_editing = false;
$message = "";

// 2. Handle form submission (Add or Update Goal)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $goal_id = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);
    $goal_start_date = filter_input(INPUT_POST, 'goal_start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $goal_end_date = filter_input(INPUT_POST, 'goal_end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $goal_books_to_read = filter_input(INPUT_POST, 'goal_books_to_read', FILTER_VALIDATE_INT);
    $goal_status = filter_input(INPUT_POST, 'goal_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

     if ($goal_books_to_read === false) {
        $message = "Invalid number of books.";
    }

    if ($is_editing && empty($goal_id))
    {
        $message = "Invalid goal ID.";
    }


    if (empty($message)) {
        if ($is_editing && !empty($goal_id)) {
            // Update goal
            $sql = "UPDATE reading_goals SET start_date = ?, end_date = ?, books_to_read = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisi", $goal_start_date, $goal_end_date, $goal_books_to_read, $goal_status, $goal_id);
             if ($stmt->execute()) {
                $message = "Goal updated successfully!";
            } else {
                $message = "Error updating goal: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Add new goal
            $sql = "INSERT INTO reading_goals (start_date, end_date, books_to_read, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $goal_start_date, $goal_end_date, $goal_books_to_read, $goal_status);
            if ($stmt->execute()) {
                $message = "Goal added successfully!";
                $goal_id = $conn->insert_id; // Get the ID of the newly inserted goal
            } else {
                $message = "Error adding goal: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// 3. Handle delete goal
if (isset($_GET['delete'])) {
    $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($delete_id) {
        $sql = "DELETE FROM reading_goals WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "Goal deleted successfully!";
        } else {
            $message = "Error deleting goal: " . $stmt->error;
        }
        $stmt->close();
    }
}


// 4. Check if we're editing a goal
if (isset($_GET['id'])) {
    $goal_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($goal_id) {
        // Fetch the goal data for editing
        $sql = "SELECT * FROM reading_goals WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $goal_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $goal = $result->fetch_assoc();
            $goal_start_date = $goal['start_date'];
            $goal_end_date = $goal['end_date'];
            $goal_books_to_read = $goal['books_to_read'];
            $goal_status = $goal['status'];
            $is_editing = true;
        } else {
            $message = "Goal not found.";
        }
        $stmt->close();
    }
}

// 5. Fetch all goals for display
$sql_select_all = "SELECT * FROM reading_goals";
$result_all_goals = $conn->query($sql_select_all);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? "Edit Reading Goal" : "Set Reading Goal"; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body, h1, p, ul, li {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
        body {
            
            background-color: #f8f9fa;
        }
        header {
            background-color: black;
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

        a {
            text-decoration: none;
            color: white;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .form-group label {
            font-weight: 600;
            color: #34495e;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-secondary {
            background-color: #e9ecef;
            border-color: #e9ecef;
            color: #34495e;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #d3d9df;
            border-color: #d3d9df;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .alert {
            margin-top: 20px;
        }
        .table-responsive {
            margin-top: 30px;
        }
        .table thead th {
            background-color: #f0f0f0;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table tbody tr:hover {
            background-color: #edf2f7;
            transition: background-color 0.3s ease;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #343a40;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
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
                <li><a href="reading_progress.php">Reading-progress Chart</a></li>
                <li><a href="reading_goals.php">Reading Goals</a></li>
                
            </ul>
        </nav>
    </header>
    <div class="container">
        <h1><?php echo $is_editing ? "Edit Reading Goal" : "Set Reading Goal"; ?></h1>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="goal_id" value="<?php echo $goal_id; ?>">
            <div class="form-group">
                <label for="goal_start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="goal_start_date" name="goal_start_date" value="<?php echo htmlspecialchars($goal_start_date); ?>" required>
            </div>
            <div class="form-group">
                <label for="goal_end_date">End Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="goal_end_date" name="goal_end_date" value="<?php echo htmlspecialchars($goal_end_date); ?>" required>
            </div>
            <div class="form-group">
                <label for="goal_books_to_read">Number of Books to Read <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="goal_books_to_read" name="goal_books_to_read" value="<?php echo htmlspecialchars($goal_books_to_read); ?>" required>
            </div>
            <div class="form-group">
                <label for="goal_status">Status <span class="text-danger">*</span></label>
                <select class="form-control" id="goal_status" name="goal_status" required>
                    <option value="" <?php echo empty($goal_status) ? 'selected' : ''; ?>>Select Status</option>
                    <option value="Not Started" <?php echo $goal_status == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="In Progress" <?php echo $goal_status == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo $goal_status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $is_editing ? "Update Goal" : "Set Goal"; ?></button>
            <a href="reading_goals.php" class="btn btn-secondary ml-2">Cancel</a>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Books to Read</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_all_goals->num_rows > 0): ?>
                        <?php while($row = $result_all_goals->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["id"]; ?></td>
                                <td><?php echo $row["start_date"]; ?></td>
                                <td><?php echo $row["end_date"]; ?></td>
                                <td><?php echo $row["books_to_read"]; ?></td>
                                <td><?php echo $row["status"]; ?></td>
                                <td>
                                    <a href="reading_goals.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="reading_goals.php?delete=<?php echo $row["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this goal?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No reading goals found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#goal_start_date, #goal_end_date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
    <footer>
        <p>&copy; 2025 Book Lovers. All rights reserved.</p>
    </footer>
</body>
</html>