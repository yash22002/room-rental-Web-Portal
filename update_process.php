<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $propertyType = $_POST['propertyType'];
    $userType = $_POST['userType'];
    $furnished = $_POST['furnished'];
    $rentMin = $_POST['rentMin'];
    $rentMax = $_POST['rentMax'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    
    $amenities = isset($_POST['amenities']) ? implode(", ", (array)$_POST['amenities']) : "";

    $sql = "UPDATE listings SET 
            city = '$city', 
            property_type = '$propertyType',
            suitable_for = '$userType',
            furnished = '$furnished',
            rent_min = '$rentMin', 
            rent_max = '$rentMax', 
            description = '$desc',
            amenities = '$amenities'
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Full Property Details Updated!'); window.location='Owner.php';</script>";
    } else {
        echo "Update Failed: " . mysqli_error($conn);
    }
}
?>
