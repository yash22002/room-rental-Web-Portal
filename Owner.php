<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Owner') {
    header("Location: Account.html");
    exit();   
}

$owner_id = $_SESSION['user_id'];
$search_query = "";
$where_clause = "WHERE owner_id = '$owner_id' AND status = 'Available'";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND (city LIKE '%$search_query%' OR property_type LIKE '%$search_query%')";
}

$sql = "SELECT * FROM listings $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$total_active = mysqli_num_rows($result);
$total_loss_all = 0;
while($r = mysqli_fetch_assoc($result)) {
    $days = (new DateTime())->diff(new DateTime($r['vacancy_start_date']))->days;
    $total_loss_all += $days * ($r['rent_min'] / 30);
}
mysqli_data_seek($result, 0); 

$enq_count_res = mysqli_query($conn, "SELECT COUNT(*) as total_enq FROM enquiries e JOIN listings l ON e.listing_id = l.id WHERE l.owner_id = '$owner_id'");
$enq_count = mysqli_fetch_assoc($enq_count_res)['total_enq'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard | Lead Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #121212; color: #ffffff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #1a1a1a !important; border-bottom: 1px solid #333; }
        .stat-card { border: none; border-radius: 15px; padding: 20px; color: white; margin-bottom: 20px; }
        .metric-card { border: none; border-radius: 15px; background: #1e1e1e; box-shadow: 0 4px 20px rgba(0,0,0,0.5); padding: 20px; margin-bottom: 30px; }
        .carousel-img { height: 250px; object-fit: cover; border-radius: 12px; }
        .loss-box { border-left: 5px solid #dc3545; background: #2d1a1a; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .review-section { background: #252525; border-radius: 10px; padding: 12px; margin-top: 15px; max-height: 150px; overflow-y: auto; border: 1px solid #333; }
        .star-rating { color: #ffc107; }
        .contact-link { text-decoration: none; color: #0d6efd; font-weight: bold; }
        .time-badge { background: rgba(13, 110, 253, 0.1); color: #0d6efd; padding: 2px 8px; border-radius: 5px; font-size: 0.75rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="#">OwnerPortal</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="Listing.html" class="btn btn-primary btn-sm rounded-pill px-4 me-3 fw-bold">+ Add Property</a>
            <span class="text-white-50 me-3 small">Welcome, <b><?php echo $_SESSION['user_name']; ?></b></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row mb-5 text-center">
        <div class="col-md-4"><div class="stat-card bg-primary"><h6>Active</h6><h2><?php echo $total_active; ?></h2></div></div>
        <div class="col-md-4"><div class="stat-card bg-danger"><h6>Total Loss</h6><h2>₹<?php echo round($total_loss_all); ?></h2></div></div>
        <div class="col-md-4"><div class="stat-card bg-success"><h6>Total Enquiries</h6><h2><?php echo $enq_count; ?></h2></div></div>
    </div>

    <h4 class="fw-bold mb-4">Property Management</h4>
    <div class="row">
        <?php while($row = mysqli_fetch_assoc($result)): 
            $lid = $row['id'];
            $days = (new DateTime())->diff(new DateTime($row['vacancy_start_date']))->days;
            $daily = $row['rent_min'] / 30;
            $m_res = mysqli_query($conn, "SELECT file_path FROM property_media WHERE listing_id = '$lid'");
        ?>
        <div class="col-md-6">
            <div class="metric-card">
                <div id="carousel_<?php echo $lid; ?>" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php $active = true; while($m = mysqli_fetch_assoc($m_res)): ?>
                            <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                                <img src="<?php echo $m['file_path']; ?>" class="carousel-img d-block w-100">
                            </div>
                        <?php $active = false; endwhile; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel_<?php echo $lid; ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel_<?php echo $lid; ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                </div>

                <h5 class="fw-bold text-uppercase"><?php echo $row['city']; ?> - ₹<?php echo $row['rent_min']; ?></h5>
                
                <div class="loss-box">
                    <div class="d-flex justify-content-between small text-danger fw-bold mb-2">
                        <span><i class="bi bi-clock-history me-1"></i>Vacancy Loss Clock</span>
                        <span class="badge bg-danger"><?php echo $days; ?> Days Idle</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-white-50">Daily Loss: ₹<?php echo round($daily); ?></span>
                        <span class="fw-bold">Total Loss: ₹<?php echo round($days * $daily); ?></span>
                    </div>
                </div>

                <div class="review-section">
                    <h6 class="small fw-bold text-info border-bottom border-secondary pb-1">Feedback</h6>
                    <?php 
                    $revs = mysqli_query($conn, "SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.listing_id = '$lid' LIMIT 2");
                    if(mysqli_num_rows($revs) > 0): while($rv = mysqli_fetch_assoc($revs)): ?>
                        <div class="mb-2 border-bottom border-secondary pb-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="fw-bold text-white"><?php echo $rv['full_name']; ?></small>
                                <span class="star-rating small"><?php echo str_repeat("⭐", $rv['rating']); ?></span>
                            </div>
                            <p class="small text-white-50 mb-0">"<?php echo $rv['comment']; ?>"</p>
                        </div>
                    <?php endwhile; else: echo "<small class='text-muted'>No reviews yet.</small>"; endif; ?>
                </div>

                <div class="row g-2 mt-3">
                    <div class="col-6"><a href="edit_property.php?id=<?php echo $lid; ?>" class="btn btn-outline-info btn-sm w-100">Edit</a></div>
                    <div class="col-6"><a href="mark_rented.php?id=<?php echo $lid; ?>" class="btn btn-success btn-sm w-100" onclick="return confirm('Stop Vacancy Loss Clock?')">Mark Rented</a></div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <h4 class="fw-bold mt-5 mb-4 text-primary">Inbound Leads & Contact Details</h4>
    <div class="table-responsive bg-dark p-3 rounded-4 shadow-sm border border-secondary mb-5">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead class="text-muted small uppercase">
                <tr>
                    <th class="ps-3">Property</th>
                    <th>Tenant</th>
                    <th>Contact Info</th>
                    <th>Message</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $enq_sql = "SELECT e.*, l.city, u.full_name, u.mobile, u.email FROM enquiries e 
                           JOIN listings l ON e.listing_id = l.id 
                           JOIN users u ON e.tenant_id = u.id 
                           WHERE l.owner_id = '$owner_id' ORDER BY e.created_at DESC";
                $enq_res = mysqli_query($conn, $enq_sql);
                
                if(mysqli_num_rows($enq_res) > 0):
                    while($e = mysqli_fetch_assoc($enq_res)): ?>
                    <tr>
                        <td class="ps-3"><b><?php echo strtoupper($e['city']); ?></b></td>
                        <td><?php echo $e['full_name']; ?></td>
                        <td>
                            <div class="small">
                                <a href="tel:<?php echo $e['mobile']; ?>" class="contact-link"><i class="bi bi-telephone me-1"></i><?php echo $e['mobile']; ?></a><br>
                                <span class="text-white-50"><i class="bi bi-envelope me-1"></i><?php echo $e['email']; ?></span>
                            </div>
                        </td>
                        <td><small class="text-white-50 italic">"<?php echo $e['message']; ?>"</small></td>
                        <td>
                            <div class="small fw-bold"><?php echo date('d M, Y', strtotime($e['created_at'])); ?></div>
                            <span class="time-badge"><i class="bi bi-clock me-1"></i><?php echo date('h:i A', strtotime($e['created_at'])); ?></span>
                        </td>
                    </tr>
                    <?php endwhile; 
                else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No leads received yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
