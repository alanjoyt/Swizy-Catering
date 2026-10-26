<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

// Get Stats
$total_bookings = $dao->count("id", "bookings", "customer_id=$user_id");
$pending_bookings = $dao->count("id", "bookings", "customer_id=$user_id AND booking_status='pending'");
$approved_bookings = $dao->count("id", "bookings", "customer_id=$user_id AND booking_status='approved'");

// Recent Bookings
$recent_bookings = $dao->getDataJoin(
    array("bookings.id", "menu_packages.package_name", "menu_packages.menu_items", "bookings.is_custom", "bookings.custom_items", "bookings.event_type", "bookings.event_date", "bookings.booking_status"),
    "bookings",
    "customer_id=$user_id ORDER BY bookings.id DESC LIMIT 5",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="brand-font">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?></h4>
        <p class="text-muted">Manage your catering bookings and explore our premium menus.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-3 text-center border-start border-warning border-4">
            <h5 class="text-muted mb-2">Total Events</h5>
            <h3 class="text-gold" style="color: var(--primary-gold);"><?= $total_bookings ? $total_bookings : 0 ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 text-center border-start border-info border-4">
            <h5 class="text-muted mb-2">Pending Requests</h5>
            <h3 class="text-info"><?= $pending_bookings ? $pending_bookings : 0 ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 text-center border-start border-success border-4">
            <h5 class="text-muted mb-2">Approved Events</h5>
            <h3 class="text-success"><?= $approved_bookings ? $approved_bookings : 0 ?></h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Recent Bookings</h5>
                <a href="bookings.php" class="btn btn-sm btn-outline-gold" style="color: var(--primary-gold); border-color: var(--primary-gold);">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-dark mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event Type</th>
                                <th>Package</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recent_bookings)): foreach($recent_bookings as $bk): ?>
                            <tr>
                                <td>#<?= $bk['id'] ?></td>
                                <td><?= htmlspecialchars($bk['event_type']) ?></td>
                                <td>
                                    <?php if($bk['is_custom']): ?>
                                        <i class="fa fa-list-check text-gold"></i> Custom Menu (<?php $c_items = json_decode($bk['custom_items']); echo is_array($c_items) ? count($c_items) : 0; ?> items)
                                    <?php else: ?>
                                        <?= htmlspecialchars($bk['package_name']) ?> (<?php $p_items = json_decode($bk['menu_items'], true); echo is_array($p_items) ? count($p_items) : 0; ?> items)
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($bk['event_date'])) ?></td>
                                <td>
                                    <?php if($bk['booking_status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif($bk['booking_status'] == 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif($bk['booking_status'] == 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst($bk['booking_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted">You have no recent bookings.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-4">
            <i class="fa fa-calendar-plus fa-4x mb-3 text-gold" style="color: var(--primary-gold);"></i>
            <h5 class="brand-font">Plan Your Next Event</h5>
            <p class="text-muted small">Experience the luxury of Swizy catering for your special day. Book now to reserve your date.</p>
            <a href="book.php" class="btn btn-gold mt-2">Book Event Now</a>
        </div>
        
        <div class="card text-center p-4 mt-4" style="background: rgba(212, 175, 55, 0.05); border-color: var(--primary-gold);">
            <i class="fa fa-robot fa-3x mb-3 text-gold" style="color: var(--primary-gold);"></i>
            <h5 class="brand-font">Unsure what to order?</h5>
            <p class="text-muted small">Let our Smart AI Recommendation engine suggest the perfect menu package for your event.</p>
            <a href="../smart_menu.php" class="btn btn-outline-gold mt-2">Try Smart Menu</a>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
