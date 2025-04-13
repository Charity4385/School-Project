<?php
include("db.php");
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = $_POST['email'];
  $password = $_POST['password'];

  $conn = new mysqli("localhost", "root", "", "data");

  // Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $password = $_POST["password"];

  // Prepare SQL statement
  $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";

  // Execute the query
  $result = $conn->query($sql);

  // Check if a user with the provided credentials exists
  if ($result->num_rows > 0) {
      // Login successful
      echo "Login successful!";
      header("location: book_details.php");
      exit();
  } else {
      // Login failed
      echo "Invalid email or password.";
  }
}
  $conn->close();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>

    <div class="login">
        <h1>Login</h1>
        <h4>It's free and only takes a minute</h4>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <input type="submit" name="" value="Submit">
        </form>

        <p>You don't have an account? <a href="signup.php">signup Here</a></p>
    </div>
</body>
</html>
