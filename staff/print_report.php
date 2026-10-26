<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['chef', 'server', 'site_manager'])) {
    header("Location: ../login.php");
    exit();
}

$dao = new DataAccess();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_data = $dao->getData("*", "users", "id=$user_id");
$user_name = (!empty($user_data) && is_array($user_data)) ? $user_data[0]['name'] : 'Staff';

$rates = array("chef" => 1300, "server" => 650, "site_manager" => 1500);
$my_rate = isset($rates[$role]) ? $rates[$role] : 0;

$report_type = isset($_GET['type']) ? $_GET['type'] : 'income';
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$filter_month = isset($_GET['month']) ? $_GET['month'] : 'all';
$filter_date = isset($_GET['exact_date']) ? $_GET['exact_date'] : '';

if (empty($filter_date) && empty($filter_year)) {
    $filter_year = date('Y');
}

$results = array();
$total_income = 0;
$errors = array();

if(!empty($filter_date)) {
    $d = DateTime::createFromFormat('Y-m-d', $filter_date);
    if (!($d && $d->format('Y-m-d') === $filter_date)) {
        $errors[] = "Invalid exact date format. Please select a valid date.";
        $filter_date = '';
    } else {
        if ($report_type == 'income' && strtotime($filter_date) > strtotime(date('Y-m-d'))) {
            $errors[] = "You cannot generate income reports for future dates.";
            $filter_date = '';
        }
    }
} else {
    if ($report_type == 'income') {
        $current_year = intval(date('Y'));
        $current_month = intval(date('m'));
        $selected_year = intval($filter_year);
        $selected_month = $filter_month == 'all' ? 12 : intval($filter_month);
        
        if ($selected_year > $current_year || ($selected_year == $current_year && $filter_month != 'all' && $selected_month > $current_month)) {
            $errors[] = "You cannot generate income reports for future periods.";
            $filter_year = date('Y');
            $filter_month = 'all';
        }
    }
}

if($report_type == 'income') {
    $cond = "booking_staff.user_id=$user_id AND bookings.booking_status='completed'";
    if(!empty($filter_date)) {
        $cond .= " AND bookings.event_date='$filter_date'";
    } else {
        $cond .= " AND YEAR(bookings.event_date)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(bookings.event_date)='$filter_month'";
    }
    
    $results = $dao->getDataJoin(
        array("bookings.event_date", "bookings.event_type", "bookings.location"),
        "booking_staff",
        "$cond ORDER BY bookings.event_date DESC",
        array("bookings" => array("booking_staff.booking_id=bookings.id", "innerjoin"))
    );
    
    $total_events = is_array($results) ? count($results) : 0;
    $total_income = $total_events * $my_rate;
}
elseif($report_type == 'leave') {
    $cond = "staff_id=$user_id AND status='approved'";
    if(!empty($filter_date)) {
        $cond .= " AND leave_date='$filter_date'";
    } else {
        $cond .= " AND YEAR(leave_date)='$filter_year'";
        if($filter_month != 'all') $cond .= " AND MONTH(leave_date)='$filter_month'";
    }
    
    $results = $dao->getData("*", "leave_requests", "$cond ORDER BY leave_date DESC");
}

$filter_desc = "";
if (!empty($filter_date)) {
    $filter_desc = "For date: " . date('d M Y', strtotime($filter_date));
} else {
    $month_name = ($filter_month == 'all') ? "All Months" : date('F', mktime(0,0,0,$filter_month,10));
    $filter_desc = "Period: " . $month_name . " " . $filter_year;
}

$report_titles = array(
    'income' => 'Earnings Report',
    'leave' => 'My Leave History'
);
$report_title = isset($report_titles[$report_type]) ? $report_titles[$report_type] : 'Staff Report';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swizy - Staff Report</title>
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
            <p class="text-muted mb-0"><i class="fa fa-user-shield me-1"></i> Staff Member Statement</p>
        </div>
        <div class="col-5 text-end">
            <h3 class="fw-bold mb-1 text-uppercase text-secondary"><?= htmlspecialchars($report_title) ?></h3>
            <p class="mb-1 text-dark">Staff Name: <strong><?= htmlspecialchars($user_name) ?></strong> (<span class="text-uppercase"><?= htmlspecialchars($role) ?></span>)</p>
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


    <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="text-secondary fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            Found <?= is_array($results) ? count($results) : 0 ?> Records
        </span>
        <?php if($report_type == 'income'): ?>
            <span class="fs-5 fw-bold text-success">Total Earnings: ₹<?= number_format($total_income, 2) ?> (@ ₹<?= number_format($my_rate) ?> / event)</span>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <?php if($report_type == 'income'): ?>
                        <th>Date Worked</th><th>Event Type</th><th>Location</th><th>Earnings</th>
                    <?php elseif($report_type == 'leave'): ?>
                        <th>Leave Date</th><th>Reason</th><th>Status</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($results)): foreach($results as $r): ?>
                    <tr>
                        <?php if($report_type == 'income'): ?>
                            <td><strong><?= date('d M Y', strtotime($r['event_date'])) ?></strong></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['event_type']) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($r['location']) ?></small></td>
                            <td class="text-success fw-bold">₹<?= number_format($my_rate, 2) ?></td>
                        <?php elseif($report_type == 'leave'): ?>
                            <td><strong><?= date('d M Y', strtotime($r['leave_date'])) ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($r['reason']) ?: 'N/A' ?></small></td>
                            <td><span class="badge" style="background-color: #d1e7dd !important; color: #0f5132 !important; border-color: #badbcc !important;">Approved</span></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-5">No records found.</td></tr>
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
