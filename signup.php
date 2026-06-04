<?php
include 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $fullname = mysqli_real_escape_string($conn, $_POST['full_name']);
    $mobile   = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $checkEmail = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists! Please login.'); window.location='Account.html';</script>";
    } else {
        $sql = "INSERT INTO users (full_name, email, mobile, password, user_role) 
                VALUES ('$fullname', '$email', '$mobile', '$hashed_password', '$role')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration Successful! Please Login.'); window.location='Account.html';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
