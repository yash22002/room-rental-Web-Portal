<?php

include 'config.php'; 

echo "<h2 style='font-family:sans-serif;'>Broker-Free Rental Portal: Table Setup</h2>";

$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        mobile VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_role ENUM('Tenant', 'Owner') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "listings" => "CREATE TABLE IF NOT EXISTS listings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT,
        city VARCHAR(50) NOT NULL,
        property_type ENUM('PG', 'Flat', 'Shared Room') NOT NULL,
        suitable_for ENUM('Student', 'Employee', 'Others') NOT NULL,
        furnished ENUM('Yes', 'No') NOT NULL,
        rent_min INT NOT NULL,
        rent_max INT NOT NULL,
        description TEXT,
        amenities TEXT, 
        vacancy_start_date DATE, 
        trust_score INT DEFAULT 100, 
        heat_score DECIMAL(3,2) DEFAULT 0.0, 
        status ENUM('Available', 'Rented') DEFAULT 'Available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "property_media" => "CREATE TABLE IF NOT EXISTS property_media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT,
        file_path VARCHAR(255) NOT NULL,
        file_type ENUM('image', 'video') NOT NULL,
        FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
    )",

    "analytics" => "CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT,
        view_count INT DEFAULT 0,
        contact_clicks INT DEFAULT 0,
        FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
    )",

    "enquiries" => "CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT,
        listing_id INT,
        message TEXT,
        status ENUM('Pending', 'Replied') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tenant_id) REFERENCES users(id),
        FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
    )",

    "reviews" => "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        listing_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT DEFAULT 5,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $tableName => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green; font-family:sans-serif;'>✅ Table <b>'$tableName'</b> created successfully!</p>";
    } else {
        echo "<p style='color:red; font-family:sans-serif;'>❌ Error creating table '$tableName': " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);
?>
