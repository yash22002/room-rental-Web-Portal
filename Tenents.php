<?php
include 'config.php';


$max_res = mysqli_query($conn, "SELECT MAX(view_count) as max_v FROM analytics");
$max_row = mysqli_fetch_assoc($max_res);
$max_val = ($max_row['max_v'] > 0) ? $max_row['max_v'] : 1;

$conditions = ["l.status = 'Available'"];

if (!empty($_GET['city'])) {
    $city = mysqli_real_escape_string($conn, $_GET['city']);
    $conditions[] = "l.city LIKE '%$city%'";
}
if (!empty($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $conditions[] = "l.property_type = '$type'";
}
if (!empty($_GET['max_rent'])) {
    $max_rent = mysqli_real_escape_string($conn, $_GET['max_rent']);
    $conditions[] = "l.rent_min <= $max_rent";
}

$where_sql = implode(" AND ", $conditions);


$sql = "SELECT l.*, u.full_name, a.view_count 
        FROM listings l 
        JOIN users u ON l.owner_id = u.id 
        JOIN analytics a ON l.id = a.listing_id 
        WHERE $where_sql 
        ORDER BY a.view_count DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenants Portal | Smart Filters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; }
        .filter-sidebar { background: #1e1e1e; border-radius: 15px; padding: 20px; border: 1px solid #333; position: sticky; top: 20px; }
        .room-card { background: #1e1e1e; border: 1px solid #333; border-radius: 18px; transition: 0.3s; overflow: hidden; }
        .room-card:hover { transform: translateY(-5px); border-color: #0d6efd; }
        .property-img { height: 180px; object-fit: cover; width: 100%; border-bottom: 1px solid #333; }
        .savings-badge { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px dashed #2ecc71; border-radius: 10px; padding: 10px; font-size: 0.75rem; text-align: center; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-3">
            <div class="filter-sidebar shadow">
                <h5 class="fw-bold mb-3 text-primary">Search Filters</h5>
                <form method="GET" action="Tenents.php">
                    <div class="mb-3">
                        <label class="small text-muted">City</label>
                        <input type="text" name="city" class="form-control form-control-sm bg-dark text-white border-secondary" 
                               placeholder="e.g. Delhi" value="<?php echo $_GET['city'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Property Type</label>
                        <select name="type" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">Any</option>
                            <option value="PG" <?php if(($_GET['type'] ?? '') == 'PG') echo 'selected'; ?>>PG</option>
                            <option value="Flat" <?php if(($_GET['type'] ?? '') == 'Flat') echo 'selected'; ?>>Flat</option>
                            <option value="Shared Room" <?php if(($_GET['type'] ?? '') == 'Shared Room') echo 'selected'; ?>>Shared Room</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Max Budget (₹)</label>
                        <input type="number" name="max_rent" class="form-control form-control-sm bg-dark text-white border-secondary" 
                               placeholder="e.g. 15000" value="<?php echo $_GET['max_rent'] ?? ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill">Apply Filters</button>
                    <a href="Tenents.php" class="btn btn-link btn-sm w-100 text-muted mt-2 text-decoration-none">Reset All</a>
                </form>
            </div>
        </div>

        <div class="col-md-9">
            <h3 class="fw-bold mb-4">Available Listings</h3>
            <div class="row g-4">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $lid = $row['id'];
                        $img_q = mysqli_query($conn, "SELECT file_path FROM property_media WHERE listing_id = '$lid' LIMIT 1");
                        $img = mysqli_fetch_assoc($img_q);
                        $src = ($img) ? $img['file_path'] : 'https://via.placeholder.com/400x200';
                        
                        $heat = ($row['view_count'] / $max_val) * 100;
                        if($heat < 10) $heat = 10;
                    ?>
                    <div class="col-md-6">
                        <div class="card room-card shadow-sm">
                            <img src="<?php echo $src; ?>" class="property-img">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0"><?php echo strtoupper($row['city']); ?></h5>
                                    <span class="badge bg-success" style="font-size: 0.65rem;">Trust Score: <?php echo $row['trust_score']; ?>%</span>
                                </div>
                                <p class="text-muted small mb-2"><?php echo $row['property_type']; ?> | Rent: ₹<?php echo $row['rent_min']; ?></p>
                                
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Heat Score:</span>
                                    <span class="text-warning fw-bold"><?php echo round($heat/10, 1); ?>/10</span>
                                </div>
                                <div class="progress mb-3" style="height: 5px; background: #333;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $heat; ?>%"></div>
                                </div>

                                <div class="savings-badge mb-3">
                                    <b>Estimated Savings: ₹<?php echo number_format($row['rent_min']); ?></b>
                                    <span class="d-block opacity-75" style="font-size: 0.65rem;">Saved via Zero Brokerage</span>
                                </div>
                                
                                <a href="view_property.php?id=<?php echo $lid; ?>" class="btn btn-outline-primary btn-sm w-100 rounded-pill">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Koi rooms nahi mile aapke filters ke hisaab se.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
