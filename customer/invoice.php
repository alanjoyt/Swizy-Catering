<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$ids_str = isset($_GET['ids']) ? preg_replace('/[^0-9,]/', '', $_GET['ids']) : '';
if(empty($ids_str)) {
    die("Invalid Invoice Request");
}

// Fetch bookings & user info
$bookings = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name", "users.name as customer_name", "users.email", "users.phone"),
    "bookings",
    "bookings.customer_id=$user_id AND bookings.id IN ($ids_str)",
    array(
        "menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"),
        "users" => array("bookings.customer_id=users.id", "innerjoin")
    )
);

if(empty($bookings)) {
    die("No bookings found.");
}

$customer = $bookings[0]; // All from same customer
$invoice_number = "INV-" . date('Y') . "-" . str_pad($bookings[0]['id'], 5, '0', STR_PAD_LEFT);
$invoice_date = date('d M Y');

$grand_total = 0;
$total_advance = 0;
$fully_paid = true;
foreach($bookings as $bk) {
    $grand_total += $bk['total_amount'];
    if($bk['payment_status'] == 'fully_paid') {
        $total_advance += $bk['total_amount'];
    } else {
        $total_advance += $bk['advance_amount'];
        $fully_paid = false;
    }
}
$balance_due = $grand_total - $total_advance;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $invoice_number ?> - Swizy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; color: #333; }
        .invoice-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-top: 8px solid #d4af37; }
        .brand-font { font-family: 'Playfair Display', serif; }
        .text-gold { color: #b8860b; }
        .bg-gold { background-color: #d4af37; color: #000; }
        .invoice-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .table th { background-color: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .table td, .table th { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .totals-section { background: #fafafa; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .terms-section { margin-top: 40px; font-size: 0.85rem; color: #666; border-left: 4px solid #d4af37; padding-left: 15px; }
        @media print {
            body { background: #fff; }
            .invoice-container { box-shadow: none; margin: 0; padding: 0; border: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container invoice-container">
    <!-- Header -->
    <div class="row invoice-header align-items-center">
        <div class="col-sm-6">
            <h1 class="brand-font text-gold m-0">SWIZY</h1>
            <p class="text-muted m-0" style="font-size: 0.9rem; letter-spacing: 1px;">PREMIUM CATERING</p>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <h4 class="text-uppercase text-muted mb-1">Invoice</h4>
            <strong><?= $invoice_number ?></strong><br>
            <span class="text-muted">Date: <?= $invoice_date ?></span>
        </div>
    </div>

    <!-- Details -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.8rem;">Billed To:</h6>
            <h5 class="mb-1"><?= htmlspecialchars($customer['customer_name']) ?></h5>
            <div><?= htmlspecialchars($customer['email']) ?></div>
            <div><?= htmlspecialchars($customer['phone']) ?></div>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.8rem;">Event Location:</h6>
            <div><?= htmlspecialchars($customer['location']) ?></div>
            <?php if(!empty($customer['special_notes'])): ?>
                <div class="mt-2 text-muted small"><strong>Notes:</strong> <?= htmlspecialchars($customer['special_notes']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Day / Date</th>
                    <th>Event Type</th>
                    <th>Menu Description</th>
                    <th class="text-center">Guests</th>
                    <th class="text-end">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $i => $bk): ?>
                <tr>
                    <td><strong>Day <?= $i+1 ?></strong><br><small class="text-muted"><?= date('d M Y', strtotime($bk['event_date'])) ?></small></td>
                    <td><?= htmlspecialchars($bk['event_type']) ?></td>
                    <td>
                        <?php 
                        if($bk['is_custom']) {
                            echo "Custom Menu";
                            $c_items = json_decode($bk['custom_items']);
                            echo " <small class='text-muted'>(" . (is_array($c_items) ? count($c_items) : 0) . " items)</small>";
                        } else {
                            echo htmlspecialchars($bk['package_name']);
                        }
                        ?>
                    </td>
                    <td class="text-center"><?= $bk['guest_count'] ?></td>
                    <td class="text-end"><?= number_format($bk['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="row justify-content-end">
        <div class="col-md-5">
            <div class="totals-section">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>₹<?= number_format($grand_total, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom text-success">
                    <span><?= $fully_paid ? 'Total Amount Paid' : 'Advance Paid (50%)' ?>:</span>
                    <span>- ₹<?= number_format($total_advance, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2 fs-5 fw-bold text-gold">
                    <span>Balance Due:</span>
                    <span>₹<?= number_format($balance_due, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms -->
    <div class="terms-section">
        <h6 class="fw-bold mb-2">Terms & Conditions</h6>
        <?php if($fully_paid): ?>
            <p class="mb-1 text-success fw-bold">This invoice has been fully paid. Thank you for your business!</p>
        <?php else: ?>
            <p class="mb-1">1. The advance payment of <strong>₹<?= number_format($total_advance, 2) ?></strong> has been received and your booking is confirmed.</p>
            <p class="mb-1">2. <strong>The balance 50% payment of ₹<?= number_format($balance_due, 2) ?> will be collected strictly immediately after the successful completion of the event.</strong></p>
            <p class="mb-0">3. Any cancellations made less than 3 days prior to the event date will not be eligible for the 45% advance refund.</p>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mt-5 pt-3 border-top no-print">
        <button onclick="window.print()" class="btn bg-gold text-dark fw-bold px-4 py-2 me-3 shadow-sm"><i class="fa fa-print me-2"></i> Print Invoice</button>
        <a href="dashboard.php" class="btn btn-dark px-4 py-2">Go to Dashboard</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
