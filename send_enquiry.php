<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login to send enquiry!'); window.location='Account.html';</script>";
        exit();
    }

    $tenant_id = $_SESSION['user_id'];
    $listing_id = mysqli_real_escape_string($conn, $_POST['listing_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO enquiries (listing_id, tenant_id, message) VALUES ('$listing_id', '$tenant_id', '$message')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Enquiry Sent Successfully!'); window.history.back();</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
