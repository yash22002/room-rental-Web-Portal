<?php
include 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: Tenents.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

mysqli_query($conn, "UPDATE analytics SET view_count = view_count + 1 WHERE listing_id = '$id'");

$sql = "SELECT l.*, u.full_name, u.mobile FROM listings l 
        JOIN users u ON l.owner_id = u.id WHERE l.id = '$id'";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) { die("<h2 style='color:white; text-align:center; margin-top:50px;'>Property Not Found!</h2>"); }

$images = mysqli_query($conn, "SELECT file_path FROM property_media WHERE listing_id = '$id'");
$rev_sql = "SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.listing_id = '$id' ORDER BY r.created_at DESC";
$rev_res = mysqli_query($conn, $rev_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $data['city']; ?> | Premium Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #121212; color: #e0e0e0; font-family: 'Inter', sans-serif; }
        .main-card { background: #1e1e1e; border-radius: 25px; border: 1px solid #333; padding: 30px; margin-top: 40px; }
        .info-box { background: #252525; border-radius: 15px; padding: 20px; border: 1px solid #333; height: 100%; }
        .property-img { height: 400px; object-fit: cover; border-radius: 20px; border: 1px solid #444; width: 100%; }
        .review-item { border-bottom: 1px solid #333; padding: 10px 0; }
        .trust-badge { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container mb-5">
    <div class="main-card shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="Tenents.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-2"></i>Back to Listings</a>
            <span class="trust-badge">Trust Score: <?php echo $data['trust_score']; ?>%</span>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <?php 
                $img_data = mysqli_fetch_assoc($images);
                $main_src = $img_data ? $img_data['file_path'] : 'https://via.placeholder.com/800x400';
                ?>
                <img src="<?php echo $main_src; ?>" class="property-img mb-3 shadow">
                
                <div class="row g-2">
                    <?php mysqli_data_seek($images, 0); while($img = mysqli_fetch_assoc($images)): ?>
                        <div class="col-3"><img src="<?php echo $img['file_path']; ?>" class="img-thumbnail bg-dark border-secondary" style="height:80px; width:100%; object-fit:cover; cursor:pointer;" onclick="this.parentElement.parentElement.previousElementSibling.src=this.src"></div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-box">
                    <h2 class="fw-bold mb-1 text-white"><?php echo strtoupper($data['city']); ?></h2>
                    <p class="text-primary fs-3 fw-bold mb-3">₹<?php echo $data['rent_min']; ?> <small class="text-muted fs-6">/ month</small></p>
                    
                    <div class="d-flex gap-3 mb-4">
                        <span class="badge bg-dark border border-secondary"><?php echo $data['property_type']; ?></span>
                        <span class="badge bg-dark border border-secondary"><?php echo $data['furnished']; ?> Furnished</span>
                    </div>

                    <p class="small text-secondary mb-4">"<?php echo $data['description']; ?>"</p>

                    <div class="p-3 border border-primary rounded-4 mb-3">
                        <h6 class="fw-bold text-white small mb-2">Interested? Send Enquiry</h6>
                        <form action="send_enquiry.php" method="POST">
                            <input type="hidden" name="listing_id" value="<?php echo $id; ?>">
                            <textarea name="message" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" placeholder="Hi, I want to visit this place..."></textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">Send Lead</button>
                        </form>
                    </div>

                    <a href="https://wa.me/91<?php echo $data['mobile']; ?>" target="_blank" class="btn btn-success w-100 py-2 rounded-pill fw-bold"><i class="bi bi-whatsapp me-2"></i>Contact via WhatsApp</a>
                </div>
            </div>
        </div>

        <hr class="my-5 border-secondary opacity-25">

        <div class="row">
            <div class="col-md-6">
                <h4 class="fw-bold text-white mb-4">Tenant Feedback</h4>
                <?php if(mysqli_num_rows($rev_res) > 0): while($rev = mysqli_fetch_assoc($rev_res)): ?>
                    <div class="review-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-info"><?php echo $rev['full_name']; ?></span>
                            <span class="text-warning"><?php echo str_repeat("⭐", $rev['rating']); ?></span>
                        </div>
                        <p class="small text-muted mt-2 mb-1">"<?php echo $rev['comment']; ?>"</p>
                        <small class="opacity-50" style="font-size: 0.7rem;"><?php echo date('d M, Y', strtotime($rev['created_at'])); ?></small>
                    </div>
                <?php endwhile; else: ?>
                    <p class="text-muted">No reviews yet. Be the first one!</p>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <div class="p-4 bg-dark rounded-4 border border-secondary">
                    <h5 class="fw-bold mb-3">Write a Review</h5>
                    <form action="submit_review.php" method="POST">
                        <input type="hidden" name="listing_id" value="<?php echo $id; ?>">
                        <div class="mb-3">
                            <label class="small text-muted mb-1">Select Rating</label>
                            <select name="rating" class="form-select bg-dark text-white border-secondary">
                                <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                                <option value="4">⭐⭐⭐⭐ (Good)</option>
                                <option value="3">⭐⭐⭐ (Average)</option>
                                <option value="2">⭐⭐ (Poor)</option>
                                <option value="1">⭐ (Very Bad)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="comment" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Share your experience about the property or owner..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Post Public Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
