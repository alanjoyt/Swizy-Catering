<?php
require_once('config/autoload.php');
$dao = new DataAccess();

$recommendations = array();
$searched = false;

if(isset($_POST['btnRecommend'])) {
    $event_type = $_POST['event_type'];
    $guest_count = intval($_POST['guest_count']);
    $budget = floatval($_POST['budget']);
    $preference = $_POST['preference']; // veg, non-veg, both

    $condition = "status=1";
    
    // Filter by preference
    if($preference == 'veg') {
        $condition .= " AND category='veg'";
    } elseif($preference == 'non-veg') {
        $condition .= " AND (category='non-veg' OR category='both')";
    }

    // Filter by budget (allow up to 20% over budget as "premium upsell" but prioritize under budget)
    $max_budget = $budget * 1.2;
    $condition .= " AND price <= $max_budget";
    
    // Filter by guest limit
    $condition .= " AND (guest_limit=0 OR guest_limit>=$guest_count)";
    
    // Order by how close the price is to the budget, and prioritize luxury/premium for weddings
    $order = "ABS(price - $budget) ASC";
    if(strtolower($event_type) == 'wedding' || strtolower($event_type) == 'vip event') {
        $order = "FIELD(package_type, 'luxury', 'premium', 'normal'), " . $order;
    }
    
    $recommendations = $dao->getData("*", "menu_packages", "$condition ORDER BY $order LIMIT 3");
    $searched = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart AI Menu Recommendation - Swizy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            padding-top: 130px; 
            background: radial-gradient(circle at top, rgba(212, 175, 55, 0.12) 0%, rgba(10, 10, 10, 1) 70%) no-repeat fixed; 
            min-height: 100vh; 
        }
        .package-img { width: 100%; height: 200px; object-fit: cover; border-top-left-radius: 15px; border-top-right-radius: 15px; border-bottom: 2px solid var(--primary-gold); }
        .glass-card {
            background: rgba(20, 20, 20, 0.65) !important;
            border: 1px solid rgba(212, 175, 55, 0.2) !important;
            backdrop-filter: blur(20px) !important;
        }
        /* Legibility & visibility enhancements */
        .text-muted {
            color: rgba(255, 255, 255, 0.65) !important;
        }
        .form-label {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.45) !important;
        }
        .form-control, .form-select {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(212, 175, 55, 0.4) !important;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: var(--primary-gold) !important;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.35) !important;
        }
        .form-select option {
            background: #1a1a1a !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top py-3">
        <div class="container">
            <a class="navbar-brand brand-font" href="index.php" style="color: var(--primary-gold); font-size: 2rem; font-weight: 700;">SWIZY</a>
            <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill border-secondary"><i class="fa fa-arrow-left"></i> Back to Home</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="text-center mb-5">
            <h1 class="brand-font text-gold" style="color: var(--primary-gold);">Smart AI Menu Recommendation</h1>
            <p class="text-muted">Tell us about your event, and our AI engine will suggest the perfect culinary packages.</p>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="glass-card p-4">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Event Type</label>
                                <select name="event_type" class="form-select" required>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Corporate">Corporate</option>
                                    <option value="Birthday">Birthday</option>
                                    <option value="Housewarming">Housewarming</option>
                                    <option value="VIP Event">VIP Event</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Estimated Guest Count</label>
                                <input type="number" name="guest_count" class="form-control" required min="10" placeholder="e.g., 200">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Budget Per Person (₹)</label>
                                <input type="number" name="budget" class="form-control" required min="100" placeholder="e.g., 1500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Dietary Preference</label>
                                <select name="preference" class="form-select" required>
                                    <option value="both">Both (Veg & Non-Veg)</option>
                                    <option value="veg">Pure Vegetarian</option>
                                    <option value="non-veg">Non-Vegetarian Focus</option>
                                </select>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" name="btnRecommend" class="btn btn-gold btn-lg px-5"><i class="fa fa-magic"></i> Get Recommendations</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if($searched): ?>
        <div class="text-center mb-4">
            <h3 class="brand-font">Top Recommended Packages For You</h3>
        </div>
        <div class="row g-4 justify-content-center">
            <?php if(!empty($recommendations)): $rank = 1; foreach($recommendations as $pkg): ?>
            <div class="col-md-4">
                <div class="glass-card h-100 position-relative">
                    <?php if($rank == 1): ?>
                    <div class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark fs-6" style="z-index: 10;"><i class="fa fa-star"></i> Best Match</div>
                    <?php endif; ?>
                    
                    <img src="assets/uploads/<?= $pkg['image'] ?>" class="package-img" onerror="this.src='https://images.unsplash.com/photo-1555244162-803834f70033?w=800&q=80'">
                    <div class="p-4">
                        <h4 class="brand-font text-gold mb-1" style="color: var(--primary-gold);"><?= htmlspecialchars($pkg['package_name']) ?></h4>
                        <div class="d-flex gap-2 mb-3 mt-2">
                            <span class="badge border border-secondary text-light"><?= ucfirst($pkg['package_type']) ?></span>
                            <span class="badge border border-secondary text-light"><?= ucfirst($pkg['category']) ?></span>
                        </div>
                        <h3 class="mb-3">₹<?= number_format($pkg['price'], 2) ?> <span class="fs-6 text-muted fw-normal">/ guest</span></h3>
                        
                        <h6 class="text-muted border-bottom border-secondary pb-2 mb-3">Menu Highlights</h6>
                        <ul class="list-unstyled text-light small mb-4">
                            <?php 
                            $items = json_decode($pkg['menu_items'], true);
                            if(is_array($items)) {
                                $display_items = array_slice($items, 0, 5); // show max 5
                                foreach($display_items as $item) {
                                    echo "<li class='mb-1'><i class='fa fa-check text-gold me-2' style='color:var(--primary-gold)'></i> " . htmlspecialchars($item) . "</li>";
                                }
                                if(count($items) > 5) echo "<li class='text-muted font-italic mt-2'>+ " . (count($items)-5) . " more items...</li>";
                            }
                            ?>
                        </ul>
                        
                        <?php 
                        $book_link = "register.php";
                        if (isset($_SESSION['user_id'])) {
                            if ($_SESSION['role'] === 'customer') {
                                $book_link = "customer/book.php?package_id=" . $pkg['id'];
                            } else {
                                $book_link = $_SESSION['role'] . "/dashboard.php";
                            }
                        }
                        ?>
                        <a href="<?= $book_link ?>" class="btn btn-outline-gold w-100 mt-auto">Book This Package</a>
                    </div>
                </div>
            </div>
            <?php $rank++; endforeach; else: ?>
            <div class="col-12 text-center">
                <div class="glass-card p-5">
                    <i class="fa fa-sad-tear fa-4x text-muted mb-3"></i>
                    <h4>No exact matches found for your criteria.</h4>
                    <p class="text-muted">Try increasing your budget or changing preferences.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
