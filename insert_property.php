<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        die("Please login first!");
    }

    $owner_id = $_SESSION['user_id'];
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $type = $_POST['propertyType'];
    $suitable = $_POST['userType'];
    $furnished = $_POST['furnished'];
    $rent_min = $_POST['rentMin'];
    $rent_max = $_POST['rentMax'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $amenities = isset($_POST['amenities']) ? implode(", ", (array)$_POST['amenities']) : "";

    $vacancy_date = date('Y-m-d'); 
    $trust_score = 100; 

    $sql = "INSERT INTO listings (owner_id, city, property_type, suitable_for, furnished, rent_min, rent_max, description, amenities, vacancy_start_date, trust_score) 
            VALUES ('$owner_id', '$city', '$type', '$suitable', '$furnished', '$rent_min', '$rent_max', '$desc', '$amenities', '$vacancy_date', '$trust_score')";

    if (mysqli_query($conn, $sql)) {
        $listing_id = mysqli_insert_id($conn);

        $upload_dir = "server/"; 
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        for ($i = 1; $i <= 5; $i++) {
            $file_key = "media" . $i;
            if (!empty($_FILES[$file_key]['name'])) {
                $file_name = time() . "_prop_" . $listing_id . "_" . $i . "_" . $_FILES[$file_key]['name'];
                $target_path = $upload_dir . $file_name;
                
                $file_type = (strpos($_FILES[$file_key]['type'], 'video') !== false) ? 'video' : 'image';

                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_path)) {
                    $sql_media = "INSERT INTO property_media (listing_id, file_path, file_type) VALUES ('$listing_id', '$target_path', '$file_type')";
                    mysqli_query($conn, $sql_media);
                }
            }
        }
        
        mysqli_query($conn, "INSERT INTO analytics (listing_id) VALUES ('$listing_id')");
        echo "<script>alert('Property Listed Successfully!'); window.location='Owner.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
