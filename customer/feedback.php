<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$booking_id) {
    echo "<script>window.location='bookings.php';</script>";
    exit();
}

// Verify booking is completed and belongs to user
$bk = $dao->getData("*", "bookings", "id=$booking_id AND customer_id=$user_id AND booking_status='completed'");
if(empty($bk)) {
    swalRedirect("Invalid request or event not yet completed.", "bookings.php", "error", "Error");
    exit();
}

// Check if feedback already exists in reviews table
$existing = $dao->getData("*", "reviews", "booking_id=$booking_id");

if(isset($_POST['btnSubmit']) && empty($existing)) {
    $rating = intval($_POST['rating']);
    $review = $_POST['review_text'];
    
    $dao->insert(array(
        "booking_id" => $booking_id,
        "user_id" => $user_id,
        "rating" => $rating,
        "review" => $review,
        "status" => 1
    ), "reviews");
    
    swalRedirect("Thank you! Your feedback has been submitted successfully.", "bookings.php", "success", "Success");
    exit();
}
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6">
        <div class="card shadow border-0" style="background:var(--card-bg);">
            <div class="card-header text-center border-0 bg-transparent py-4">
                <i class="fa fa-star fa-3x mb-3 text-gold" style="color:var(--primary-gold);"></i>
                <h4 class="brand-font m-0 text-white">Rate Your Experience</h4>
                <p class="text-muted small mt-2">Event Date: <?= date('d M Y', strtotime($bk[0]['event_date'])) ?></p>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($existing)): ?>
                    <div class="alert alert-success text-center">
                        <i class="fa fa-check-circle fa-2x mb-2"></i><br>
                        You have already submitted a <?= $existing[0]['rating'] ?>-star review for this event.<br>
                        <small class="text-muted">"<?= htmlspecialchars($existing[0]['review']) ?>"</small>
                    </div>
                    <div class="text-center mt-4">
                        <a href="bookings.php" class="btn btn-outline-light w-100">Back to Bookings</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-4 text-center">
                            <label class="form-label text-muted d-block">Overall Rating</label>
                            <div class="rating-stars fs-2" style="cursor:pointer; color: #555;">
                                <i class="fa fa-star star-btn" data-val="1"></i>
                                <i class="fa fa-star star-btn" data-val="2"></i>
                                <i class="fa fa-star star-btn" data-val="3"></i>
                                <i class="fa fa-star star-btn" data-val="4"></i>
                                <i class="fa fa-star star-btn" data-val="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Your Review</label>
                            <textarea name="review_text" class="form-control" rows="4" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" placeholder="Tell us what you loved about the catering..." required></textarea>
                        </div>
                        <button type="submit" name="btnSubmit" class="btn btn-gold w-100 py-3 fw-bold" id="submitBtn" disabled>Submit Feedback</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('ratingInput');
    const submitBtn = document.getElementById('submitBtn');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            let val = this.getAttribute('data-val');
            ratingInput.value = val;
            submitBtn.removeAttribute('disabled');
            
            stars.forEach(s => {
                if(s.getAttribute('data-val') <= val) {
                    s.style.color = 'var(--primary-gold)';
                } else {
                    s.style.color = '#555';
                }
            });
        });
    });
</script>

<?php require_once('footer.php'); ?>
