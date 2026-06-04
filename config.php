<?php
$servername = "localhost";
$username = "root";
$password = "manager";
$dbname = "Broker_Free_Room_Rental_Portal";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();
?>
