<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

// Get user profile details
$user_data = $dao->getData("*", "users", "id=$user_id");
$user_name = (!empty($user_data) && is_array($user_data)) ? $user_data[0]['name'] : 'Customer';

$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$filter_month = isset($_GET['month']) ? $_GET['month'] : 'all';
$filter_date = isset($_GET['exact_date']) ? $_GET['exact_date'] : '';

if (empty($filter_date) && empty($filter_year)) {
    $filter_year = date('Y');
}

$errors = array();
if(!empty($filter_date)) {
    $d = DateTime::createFromFormat('Y-m-d', $filter_date);
    if (!($d && $d->format('Y-m-d') === $filter_date)) {
        $errors[] = "Invalid exact date format. Please select a valid date.";
        $filter_date = '';
    }
}

$cond = "customer_id=$user_id";
if(!empty($filter_date)) {
    $cond .= " AND event_date='$filter_date'";
} else {
    $cond .= " AND YEAR(event_date)='$filter_year'";
    if($filter_month != 'all') $cond .= " AND MONTH(event_date)='$filter_month'";
}

$results = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name"),
    "bookings",
    "$cond ORDER BY event_date DESC",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);

$total_spent = 0;
foreach($results as $r) {
    if($r['booking_status'] == 'completed') {
        $total_spent += $r['total_amount'];
    }
}
$filter_desc = "";
if (!empty($filter_date)) {
    $filter_desc = "For date: " . date('d M Y', strtotime($filter_date));
} else {
    $month_name = ($filter_month == 'all') ? "All Months" : date('F', mktime(0,0,0,$filter_month,10));
    $filter_desc = "Period: " . $month_name . " " . $filter_year;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swizy - Customer Bookings Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #ffffff;
            color: #212529;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            padding: 40px;
        }
        .brand-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #b8860b;
            letter-spacing: 1px;
        }
        .report-header {
            border-bottom: 2px solid #d4af37;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .table {
            color: #212529 !important;
        }
        .table th {
            background-color: #f8f9fa !important;
            color: #b8860b !important;
            border-bottom: 2px solid #d4af37 !important;
            font-weight: 600;
        }
        .table td {
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .badge {
            background-color: #e9ecef !important;
            color: #495057 !important;
            border: 1px solid #ced4da;
            padding: 5px 10px;
            font-weight: 500;
        }
        .no-print-bar {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print no-print-bar d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted"><i class="fa fa-info-circle me-1"></i> Press <strong>Print / Save PDF</strong> to download this report.</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-dark btn-sm px-3">
                <i class="fa fa-print me-1"></i> Print / Save PDF
            </button>
            <button onclick="window.close();" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fa fa-times me-1"></i> Close
            </button>
        </div>
    </div>

    <div class="report-header row align-items-center">
        <div class="col-7">
            <h1 class="brand-title mb-1">SWIZY CATERING</h1>
            <p class="text-muted mb-0"><i class="fa fa-user me-1"></i> Customer Booking Statement</p>
        </div>
        <div class="col-5 text-end">
            <h3 class="fw-bold mb-1 text-uppercase text-secondary">My Booking Report</h3>
            <p class="mb-1 text-dark">Customer Name: <strong><?= htmlspecialchars($user_name) ?></strong></p>
            <p class="mb-1 text-dark"><strong><?= htmlspecialchars($filter_desc) ?></strong></p>
            <p class="text-muted mb-0" style="font-size: 0.8rem;">Generated: <?= date('d M Y, h:i A') ?></p>
        </div>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Results Info and Summary Cards -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="text-secondary fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            Found <?= is_array($results) ? count($results) : 0 ?> Events
        </span>
        <span class="fs-5 fw-bold text-success">Total Spent (Completed): ₹<?= number_format($total_spent, 2) ?></span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Event Date</th>
                    <th>Event Type</th>
                    <th>Menu / Package</th>
                    <th>Status</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($results)): foreach($results as $r): ?>
                    <tr>
                        <td><strong><?= date('d M Y', strtotime($r['event_date'])) ?></strong></td>
                        <td class="fw-bold"><?= htmlspecialchars($r['event_type']) ?></td>
                        <td><?= $r['is_custom'] ? 'Custom Menu' : htmlspecialchars($r['package_name']) ?></td>
                        <td><span class="badge"><?= ucfirst($r['booking_status']) ?></span></td>
                        <td class="fw-bold text-dark">₹<?= number_format($r['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">No bookings found in this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
