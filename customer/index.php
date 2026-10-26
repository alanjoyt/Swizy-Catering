<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

// Get user info
$user = $dao->getData("name", "users", "id=$user_id")[0];
$first_name = htmlspecialchars(explode(' ', trim($user['name']))[0]);

// Get next upcoming booking
$upcoming = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name"),
    "bookings",
    "customer_id=$user_id AND booking_status='approved' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);

// Get popular packages for inspiration
$packages = $dao->getData("*", "menu_packages", "status=1 ORDER BY price DESC LIMIT 3");

// Count total bookings for loyalty status
$total_bookings = $dao->count("id", "bookings", "customer_id=$user_id AND booking_status='completed'");
$loyalty_tier = $total_bookings >= 5 ? "Gold Elite" : ($total_bookings >= 2 ? "Silver Member" : "Classic Member");
?>

<style>
    .welcome-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        border: 1px solid rgba(212, 175, 55, 0.3);
    }
    .welcome-bg {
        background: linear-gradient(135deg, rgba(10,10,10,0.95) 0%, rgba(30,30,30,0.8) 100%), url('../assets/images/hero-bg.jpg') center/cover;
        position: absolute;
        inset: 0;
        z-index: 0;
    }
    .welcome-content {
        position: relative;
        z-index: 1;
        padding: 4rem 3rem;
    }
    .loyalty-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid var(--primary-gold);
        color: var(--primary-gold);
        font-size: 0.85rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }
    .action-card {
        background: var(--card-bg);
        border-radius: 15px;
        border: 1px solid rgba(255,255,255,0.05);
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        position: relative;
    }
    .action-card:hover {
        transform: translateY(-5px);
        border-color: rgba(212, 175, 55, 0.4);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.1);
    }
    .action-icon-wrap {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(212, 175, 55, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .action-card:hover .action-icon-wrap {
        background: var(--primary-gold);
    }
    .action-card:hover .action-icon-wrap i {
        color: #000 !important;
    }
    .event-countdown {
        background: linear-gradient(45deg, rgba(212,175,55,0.1), rgba(212,175,55,0.02));
        border-left: 4px solid var(--primary-gold);
        padding: 1.5rem;
        border-radius: 10px;
    }
    .inspire-card {
        border-radius: 15px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.4s ease;
    }
    .inspire-card:hover {
        border-color: var(--primary-gold);
    }
    .inspire-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: all 0.5s ease;
    }
    .inspire-card:hover .inspire-img {
        transform: scale(1.05);
    }
    .inspire-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        padding: 2rem 1.5rem 1rem;
    }
</style>

<div class="container py-4">
    
    <!-- Hero Welcome Section -->
    <div class="welcome-hero">
        <div class="welcome-bg"></div>
        <div class="welcome-content d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <div class="loyalty-badge"><i class="fa fa-crown me-2"></i><?= $loyalty_tier ?></div>
                <h1 class="brand-font text-white mb-2" style="font-size: 3.5rem;">Welcome, <?= $first_name ?></h1>
                <p class="text-muted fs-5 mb-0" style="max-width: 500px;">Experience culinary excellence. Whether it's a grand wedding or an intimate gathering, we make it unforgettable.</p>
            </div>
            <div class="mt-4 mt-md-0">
                <a href="book.php" class="btn btn-gold btn-lg px-4 rounded-pill shadow-lg" style="animation: pulse 2s infinite;"><i class="fa fa-calendar-plus me-2"></i> Plan New Event</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Upcoming Event Tracker -->
        <div class="col-lg-8">
            <div class="card shadow border-0 h-100" style="background:var(--card-bg);">
                <div class="card-body p-4">
                    <h5 class="brand-font text-gold mb-4" style="color:var(--primary-gold);"><i class="fa fa-clock me-2"></i> Upcoming Event</h5>
                    
                    <?php if(!empty($upcoming)): $event = $upcoming[0]; 
                        $days_left = floor((strtotime($event['event_date']) - time()) / (60 * 60 * 24));
                    ?>
                        <div class="event-countdown d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h4 class="text-white mb-1"><?= htmlspecialchars($event['event_type']) ?></h4>
                                <p class="text-muted mb-0">
                                    <i class="fa fa-calendar-alt me-2 text-gold" style="color:var(--primary-gold);"></i> <?= date('F j, Y', strtotime($event['event_date'])) ?>
                                    <?php if(!empty($event['event_time'])): ?>
                                        &nbsp;|&nbsp; <i class="fa fa-clock me-1 text-gold" style="color:var(--primary-gold);"></i> <?= date('h:i A', strtotime($event['event_time'])) ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted mt-2 mb-0 small">
                                    <i class="fa fa-users me-1"></i> <?= $event['guest_count'] ?> Guests &nbsp;|&nbsp; 
                                    <i class="fa fa-utensils me-1"></i> <?= $event['is_custom'] ? 'Custom Menu' : htmlspecialchars($event['package_name']) ?>
                                </p>
                            </div>
                            <div class="text-md-end mt-3 mt-md-0 text-center text-md-right">
                                <div class="brand-font" style="font-size: 2.5rem; color:var(--primary-gold); line-height:1;"><?= $days_left ?></div>
                                <div class="text-uppercase text-muted" style="font-size:0.8rem; letter-spacing:2px;">Days to go</div>
                                <a href="bookings.php" class="btn btn-sm btn-outline-light mt-2 rounded-pill px-3">View Details</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3"><i class="fa fa-calendar-times fa-3x text-muted" style="opacity:0.3;"></i></div>
                            <h5 class="text-muted mb-3">No upcoming events scheduled.</h5>
                            <p class="text-muted small">Your calendar is clear. Ready to celebrate something special?</p>
                            <a href="book.php" class="btn btn-outline-gold rounded-pill mt-2">Explore Options</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats / Actions -->
        <div class="col-lg-4">
            <div class="row g-3 h-100">
                <div class="col-6 col-lg-12 h-50">
                    <a href="bookings.php" class="text-decoration-none">
                        <div class="action-card p-3 p-md-4 d-flex align-items-center">
                            <div class="action-icon-wrap me-3">
                                <i class="fa fa-clipboard-list fa-lg text-gold" style="color:var(--primary-gold);"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-1">My Bookings</h5>
                                <span class="text-muted small">View history & invoices</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-12 h-50">
                    <a href="../smart_menu.php" class="text-decoration-none">
                        <div class="action-card p-3 p-md-4 d-flex align-items-center">
                            <div class="action-icon-wrap me-3">
                                <i class="fa fa-magic fa-lg text-gold" style="color:var(--primary-gold);"></i>
                            </div>
                            <div>
                                <h5 class="text-white mb-1">AI Menu</h5>
                                <span class="text-muted small">Get smart recommendations</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspiration Section -->
    <h4 class="brand-font text-white mb-4"><i class="fa fa-star text-gold me-2" style="color:var(--primary-gold);"></i> Premium Inspiration</h4>
    <div class="row g-4">
        <?php if(!empty($packages)): foreach($packages as $pkg): ?>
        <div class="col-md-4">
            <div class="inspire-card h-100 shadow">
                <img src="../assets/uploads/<?= htmlspecialchars($pkg['image']) ?>" class="inspire-img" alt="<?= htmlspecialchars($pkg['package_name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1555244162-803834f70033?w=500&auto=format&fit=crop&q=60'">
                <div class="inspire-overlay">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h5 class="brand-font text-white mb-1"><?= htmlspecialchars($pkg['package_name']) ?></h5>
                            <span class="badge bg-dark border border-warning text-warning">₹<?= number_format($pkg['price']) ?> / guest</span>
                        </div>
                        <a href="book.php" class="btn btn-sm btn-gold rounded-circle" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center; padding:0;"><i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

</div>

<?php require_once('footer.php'); ?>
