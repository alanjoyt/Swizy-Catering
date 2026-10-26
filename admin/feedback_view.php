<?php require_once('header.php'); 
$dao = new DataAccess();

$feedbacks = $dao->getDataJoin(
    array("reviews.*", "users.name as customer_name"),
    "reviews",
    "1 ORDER BY reviews.id DESC",
    array(
        "users" => array("reviews.user_id=users.id", "innerjoin")
    )
);
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-gold" style="color:var(--primary-gold);">
            <h5 class="mb-0"><i class="fa fa-star me-2"></i> Customer Feedback & Reviews</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <?php if(!empty($feedbacks)): foreach($feedbacks as $fb): ?>
                <div class="col-md-6">
                    <div class="p-3 rounded" style="background:rgba(0,0,0,0.3); border:1px solid rgba(212,175,55,0.2);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="text-white mb-0"><?= htmlspecialchars($fb['customer_name']) ?></h6>
                                <small class="text-muted">Customer Feedback</small>
                            </div>
                            <div class="text-gold" style="color:var(--primary-gold);">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa fa-star <?= $i <= $fb['rating'] ? '' : 'text-secondary' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-light mb-1 fst-italic">"<?= htmlspecialchars($fb['review']) ?>"</p>
                        <div class="text-end">
                            <small class="text-muted" style="font-size:0.7rem;">Review #<?= $fb['id'] ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="col-12 text-center text-muted py-5">No feedback received yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
