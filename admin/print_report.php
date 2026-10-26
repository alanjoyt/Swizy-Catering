<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$dao = new DataAccess();

$report_type = isset($_GET['type']) ? $_GET['type'] : 'booking';
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$filter_month = isset($_GET['month']) ? $_GET['month'] : 'all';
$filter_date = isset($_GET['exact_date']) ? $_GET['exact_date'] : '';

if (empty($filter_date) && empty($filter_year)) {
    $filter_year = date('Y');
}

$results = array();
$total_val = 0;
$errors = array();

if(!empty($filter_date)) {
    $d = DateTime::createFromFormat('Y-m-d', $filter_date);
    if (!($d && $d->format('Y-m-d') === $filter_date)) {
        $errors[] = "Invalid exact date format. Please select a valid date.";
        $filter_date = '';
    } else {
        if (($report_type == 'revenue' || $report_type == 'salary') && strtotime($filter_date) > strtotime(date('Y-m-d'))) {
            $errors[] = "You cannot generate " . htmlspecialchars(ucfirst($report_type)) . " reports for future dates.";
            $filter_date = '';
        }
    }
} else {
    if ($report_type == 'revenue' || $report_type == 'salary') {
        $current_year = intval(date('Y'));
        $current_month = intval(date('m'));
        $selected_year = intval($filter_year);
        $selected_month = $filter_month == 'all' ? 12 : intval($filter_month);
        
        if ($selected_year > $current_year || ($selected_year == $current_year && $filter_month != 'all' && $selected_month > $current_month)) {
            $errors[] = "You cannot generate " . htmlspecialchars(ucfirst($report_type)) . " reports for future periods.";
            $filter_year = date('Y');
            $filter_month = 'all';
        }
    }
}

if($report_type == 'booking') {
    $cond = "1";
    if(!empty($filter_date)) {
        $cond .= " AND bookings.event_date='$filter_date'";
    } else {
        $cond .= " AND YEAR(bookings.event_date)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(bookings.event_date)='$filter_month'";
    }
    
    $results = $dao->getDataJoin(
        array("bookings.*", "users.name as customer_name"),
        "bookings",
        "$cond ORDER BY bookings.event_date DESC",
        array("users" => array("bookings.customer_id=users.id", "innerjoin"))
    );
} 
elseif($report_type == 'leave') {
    $cond = "leave_requests.status='approved'";
    if(!empty($filter_date)) {
        $cond .= " AND leave_requests.leave_date='$filter_date'";
    } else {
        $cond .= " AND YEAR(leave_requests.leave_date)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(leave_requests.leave_date)='$filter_month'";
    }
    
    $results = $dao->getDataJoin(
        array("leave_requests.*", "users.name as staff_name", "users.role"),
        "leave_requests",
        "$cond ORDER BY leave_requests.leave_date DESC",
        array("users" => array("leave_requests.staff_id=users.id", "innerjoin"))
    );
}
elseif($report_type == 'revenue') {
    $cond = "bookings.payment_status IN ('advance_paid', 'fully_paid', 'refunded')";
    if(!empty($filter_date)) {
        $cond .= " AND DATE(bookings.created_at)='$filter_date'";
    } else {
        $cond .= " AND YEAR(bookings.created_at)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(bookings.created_at)='$filter_month'";
    }
    
    $results = $dao->getDataJoin(
        array("bookings.id", "bookings.advance_amount", "bookings.created_at", "users.name as customer_name"),
        "bookings",
        "$cond ORDER BY bookings.created_at DESC",
        array("users" => array("bookings.customer_id=users.id", "innerjoin"))
    );
    
    if(is_array($results)) {
        foreach($results as $r) {
            $total_val += $r['advance_amount'];
        }
    }
}
elseif($report_type == 'salary') {
    $cond = "bookings.booking_status='completed'";
    if(!empty($filter_date)) {
        $cond .= " AND bookings.event_date='$filter_date'";
    } else {
        $cond .= " AND YEAR(bookings.event_date)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(bookings.event_date)='$filter_month'";
    }
    
    $sql = "SELECT u.id, u.name, u.role, COUNT(bs.id) as events_worked 
            FROM users u 
            JOIN booking_staff bs ON u.id = bs.user_id 
            JOIN bookings ON bs.booking_id = bookings.id 
            WHERE $cond 
            GROUP BY u.id, u.name, u.role 
            ORDER BY events_worked DESC";
            
    $results = $dao->query($sql);
    if($results === false) $results = array();
    
    $rates = array("chef" => 1300, "server" => 650, "site_manager" => 1500);
    foreach($results as $r) {
        $rate = isset($rates[$r['role']]) ? $rates[$r['role']] : 0;
        $total_val += ($r['events_worked'] * $rate);
    }
}

// Generate human-friendly description of filters
$filter_desc = "";
if (!empty($filter_date)) {
    $filter_desc = "For date: " . date('d M Y', strtotime($filter_date));
} else {
    $month_name = ($filter_month == 'all') ? "All Months" : date('F', mktime(0,0,0,$filter_month,10));
    $filter_desc = "Period: " . $month_name . " " . $filter_year;
}

$report_titles = array(
    'booking' => 'Booking Report',
    'revenue' => 'Revenue Report',
    'salary' => 'Staff Salary Report',
    'leave' => 'Staff Leave Report'
);
$report_title = isset($report_titles[$report_type]) ? $report_titles[$report_type] : 'Report';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swizy - Admin PDF Report</title>
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

    <!-- Controls bar (hidden when printing) -->
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

    <!-- Printable Header -->
    <div class="report-header row align-items-center">
        <div class="col-7">
            <h1 class="brand-title mb-1">SWIZY CATERING</h1>
            <p class="text-muted mb-0"><i class="fa fa-map-marker-alt me-1"></i> Luxury Catering & Events Panel</p>
        </div>
        <div class="col-5 text-end">
            <h3 class="fw-bold mb-1 text-uppercase text-secondary"><?= htmlspecialchars($report_title) ?></h3>
            <p class="mb-1 text-dark"><strong><?= htmlspecialchars($filter_desc) ?></strong></p>
            <p class="text-muted mb-0" style="font-size: 0.8rem;">Generated: <?= date('d M Y, h:i A') ?></p>
        </div>
    </div>

    <!-- Errors if any -->
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
            Results (<?= is_array($results) ? count($results) : 0 ?> Records)
        </span>
        <?php if($report_type == 'revenue'): ?>
            <span class="fs-5 fw-bold text-success">Total Revenue: ₹<?= number_format($total_val, 2) ?></span>
        <?php elseif($report_type == 'salary'): ?>
            <span class="fs-5 fw-bold text-primary">Total Payout: ₹<?= number_format($total_val, 2) ?></span>
        <?php endif; ?>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <?php if($report_type == 'booking'): ?>
                        <th>ID</th><th>Customer</th><th>Date</th><th>Guests</th><th>Status</th><th>Total Amount</th>
                    <?php elseif($report_type == 'revenue'): ?>
                        <th>Tx ID</th><th>Customer</th><th>Date Collected</th><th>Amount Collected (Revenue)</th>
                    <?php elseif($report_type == 'leave'): ?>
                        <th>Staff Name</th><th>Role</th><th>Leave Date</th><th>Reason</th>
                    <?php elseif($report_type == 'salary'): ?>
                        <th>Staff Name</th><th>Role</th><th>Events Worked</th><th>Total Salary Paid</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($results)): foreach($results as $r): ?>
                    <tr>
                        <?php if($report_type == 'booking'): ?>
                            <td>#<?= $r['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['customer_name']) ?></td>
                            <td><?= date('d M Y', strtotime($r['event_date'])) ?></td>
                            <td><?= $r['guest_count'] ?></td>
                            <td><span class="badge"><?= ucfirst($r['booking_status']) ?></span></td>
                            <td class="fw-bold">
                                <?php if($r['booking_status'] == 'cancelled'): ?>
                                    <span class="text-danger">-₹<?= number_format($r['total_amount'], 2) ?></span>
                                <?php else: ?>
                                    ₹<?= number_format($r['total_amount'], 2) ?>
                                <?php endif; ?>
                            </td>
                        <?php elseif($report_type == 'revenue'): ?>
                            <td>#<?= $r['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['customer_name']) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></td>
                            <td class="text-success fw-bold">₹<?= number_format($r['advance_amount'], 2) ?></td>
                        <?php elseif($report_type == 'leave'): ?>
                            <td class="fw-bold"><?= htmlspecialchars($r['staff_name']) ?></td>
                            <td><span class="badge text-uppercase"><?= $r['role'] ?></span></td>
                            <td><strong><?= date('d M Y', strtotime($r['leave_date'])) ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($r['reason']) ?: 'N/A' ?></small></td>
                        <?php elseif($report_type == 'salary'): 
                            $rates = array("chef" => 1300, "server" => 650, "site_manager" => 1500);
                            $rate = isset($rates[$r['role']]) ? $rates[$r['role']] : 0;
                            $salary = $r['events_worked'] * $rate;
                        ?>
                            <td class="fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                            <td><span class="badge text-uppercase"><?= $r['role'] ?></span></td>
                            <td><?= $r['events_worked'] ?></td>
                            <td class="text-primary fw-bold">₹<?= number_format($salary, 2) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No records found for the selected criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Print Init Script -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
