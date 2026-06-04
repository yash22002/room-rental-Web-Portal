<?php
include 'config.php';

if (!isset($_GET['id'])) { header("Location: Owner.php"); exit(); }

$id = mysqli_real_escape_string($conn, $_GET['id']);
$owner_id = $_SESSION['user_id'];

$sql = "SELECT * FROM listings WHERE id = '$id' AND owner_id = '$owner_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) { die("Property not found!"); }

$old_amenities = explode(", ", $data['amenities']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Full Property Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f2f3f7; padding: 40px 0; }
        .edit-card { background: #fff; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="edit-card">
                <h3 class="fw-bold mb-4 text-primary">Edit Property Details</h3>
                <form action="update_process.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">City Location</label>
                        <input type="text" name="city" class="form-control" value="<?php echo $data['city']; ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Property Type</label>
                            <select class="form-select" name="propertyType">
                                <option value="PG" <?php if($data['property_type'] == 'PG') echo 'selected'; ?>>PG</option>
                                <option value="Flat" <?php if($data['property_type'] == 'Flat') echo 'selected'; ?>>Flat</option>
                                <option value="Shared Room" <?php if($data['property_type'] == 'Shared Room') echo 'selected'; ?>>Shared Room</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Suitable For</label>
                            <select class="form-select" name="userType">
                                <option value="Student" <?php if($data['suitable_for'] == 'Student') echo 'selected'; ?>>Student</option>
                                <option value="Employee" <?php if($data['suitable_for'] == 'Employee') echo 'selected'; ?>>Employee</option>
                                <option value="Others" <?php if($data['suitable_for'] == 'Others') echo 'selected'; ?>>Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Furnished</label>
                            <select class="form-select" name="furnished">
                                <option value="Yes" <?php if($data['furnished'] == 'Yes') echo 'selected'; ?>>Yes</option>
                                <option value="No" <?php if($data['furnished'] == 'No') echo 'selected'; ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Min Rent (₹)</label>
                            <input type="number" name="rentMin" class="form-control" value="<?php echo $data['rent_min']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Max Rent (₹)</label>
                            <input type="number" name="rentMax" class="form-control" value="<?php echo $data['rent_max']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $data['description']; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold d-block">Amenities</label>
                        <?php 
                        $all_amenities = ['WiFi', 'AC', 'Parking', 'Attached Bathroom'];
                        foreach($all_amenities as $amenity):
                        ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="<?php echo $amenity; ?>" 
                            <?php if(in_array($amenity, $old_amenities)) echo 'checked'; ?>>
                            <label class="form-check-label"><?php echo $amenity; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Save All Changes</button>
                        <a href="Owner.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
