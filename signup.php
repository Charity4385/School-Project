<?php
session_start();
include("db.php");
if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $email= $_POST['email'];
    $password = $_POST['password'];

    if(!empty($email) && !empty($password) && !is_numeric($email))
    {
        $query = "INSERT INTO users(fname, lname, email, password) VALUES('$firstname', '$lastname', '$email', '$password')";
    
        mysqli_query($con, $query);
        echo "<script type='text/javascript'> alert('Successfully Reigistered')</script>";
    }
    else
    {
        echo "<script type='text/javascript'> alert('Please Enter Valid Information')</script>"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="signup">
        <h1>Signup</h1>
        <h4>It's free and only takes a minute</h4>
        <form method="POST">
            <label>First Name</label>
            <input type="text" name="fname" required>
            <label>Last Name</label>
            <input type="text" name="lname" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <input type="submit" name="" value="Submit">
        </form>
        <p>By clicking this sign up button, you agree to our <br>
        <a href="">Terms and conditions</a> and <a href="">Policy Privacy</a></p>

        <p>Already have an account? <a href="login.php">Login Here</a></p>
    </div>
</body>
</html>
