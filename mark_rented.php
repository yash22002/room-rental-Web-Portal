<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $owner_id = $_SESSION['user_id'];

    $sql = "UPDATE listings SET status = 'Rented' WHERE id = '$id' AND owner_id = '$owner_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Property Marked as Rented. Clock Stopped!'); window.location='Owner.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
