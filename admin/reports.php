<?php require_once('header.php'); 
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
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0 mb-4" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-gold border-0" style="color:var(--primary-gold);">
            <h5 class="mb-0"><i class="fa fa-chart-bar me-2"></i> Report Generator</h5>
        </div>
        <div class="card-body">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger mb-4" style="background: rgba(220,53,69,0.1); border-color: #dc3545; color: #ff6b6b;">
                    <ul class="mb-0">
                        <?php foreach($errors as $err): ?>
                            <li><?= $err ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted">Report Type</label>
                    <select name="type" class="form-control bg-dark text-white border-secondary">
                        <option value="booking" <?= $report_type=='booking'?'selected':'' ?>>Booking Report</option>
                        <option value="revenue" <?= $report_type=='revenue'?'selected':'' ?>>Revenue Report</option>
                        <option value="salary" <?= $report_type=='salary'?'selected':'' ?>>Staff Salary Report</option>
                        <option value="leave" <?= $report_type=='leave'?'selected':'' ?>>Staff Leave Report</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">Year</label>
                    <select name="year" id="filter_year" class="form-control bg-dark text-white border-secondary">
                        <option value="">-- Select Year --</option>
                        <?php for($i=date('Y')-2; $i<=date('Y')+2; $i++): ?>
                            <option value="<?= $i ?>" <?= $filter_year==$i?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted">Month</label>
                    <select name="month" id="filter_month" class="form-control bg-dark text-white border-secondary">
                        <option value="all" <?= $filter_month=='all'?'selected':'' ?>>All Months</option>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $filter_month==$i?'selected':'' ?>><?= date('F', mktime(0,0,0,$i,10)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Or Exact Date</label>
                    <input type="date" name="exact_date" id="filter_date" class="form-control bg-dark text-white border-secondary" value="<?= $filter_date ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold w-100"><i class="fa fa-filter"></i> Generate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow border-0" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-white border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-uppercase text-muted">Results (<?= is_array($results) ? count($results) : 0 ?> Records)</h6>
            <div class="d-flex align-items-center gap-3">
                <?php if($report_type == 'revenue'): ?>
                    <strong style="color:var(--primary-gold);">Total Revenue: ₹<?= number_format($total_val, 2) ?></strong>
                <?php elseif($report_type == 'salary'): ?>
                    <strong class="text-info">Total Payout: ₹<?= number_format($total_val, 2) ?></strong>
                <?php endif; ?>
                <?php if(!empty($results) && empty($errors)): ?>
                    <a href="print_report.php?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-sm btn-gold">
                        <i class="fa fa-file-pdf me-1"></i> Export PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead style="font-size:0.8rem; color:var(--primary-gold);">
                        <tr>
                            <?php if($report_type == 'booking'): ?>
                                <th class="ps-4">ID</th><th>Customer</th><th>Date</th><th>Guests</th><th>Status</th><th>Total Amount</th>
                            <?php elseif($report_type == 'revenue'): ?>
                                <th class="ps-4">Tx ID</th><th>Customer</th><th>Date Collected</th><th>Amount Collected (Revenue)</th>
                            <?php elseif($report_type == 'leave'): ?>
                                <th class="ps-4">Staff Name</th><th>Role</th><th>Leave Date</th><th>Reason</th>
                            <?php elseif($report_type == 'salary'): ?>
                                <th class="ps-4">Staff Name</th><th>Role</th><th>Events Worked</th><th>Total Salary Paid</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($results)): foreach($results as $r): ?>
                            <tr>
                                <?php if($report_type == 'booking'): ?>
                                    <td class="ps-4">#<?= $r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($r['event_date'])) ?></td>
                                    <td><?= $r['guest_count'] ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($r['booking_status']) ?></span></td>
                                    <td>
                                        <?php if($r['booking_status'] == 'cancelled'): ?>
                                            <span class="text-danger">-₹<?= number_format($r['total_amount'], 2) ?></span>
                                        <?php else: ?>
                                            ₹<?= number_format($r['total_amount'], 2) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php elseif($report_type == 'revenue'): ?>
                                    <td class="ps-4">#<?= $r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                                    <td><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></td>
                                    <td class="text-success fw-bold">₹<?= number_format($r['advance_amount'], 2) ?></td>
                                <?php elseif($report_type == 'leave'): ?>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($r['staff_name']) ?></td>
                                    <td><span class="badge bg-secondary text-uppercase"><?= $r['role'] ?></span></td>
                                    <td><strong class="text-white"><?= date('d M Y', strtotime($r['leave_date'])) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($r['reason']) ?: 'N/A' ?></small></td>
                                <?php elseif($report_type == 'salary'): 
                                    $rates = array("chef" => 1300, "server" => 650, "site_manager" => 1500);
                                    $rate = isset($rates[$r['role']]) ? $rates[$r['role']] : 0;
                                    $salary = $r['events_worked'] * $rate;
                                ?>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                    <td><span class="badge bg-secondary text-uppercase"><?= $r['role'] ?></span></td>
                                    <td><?= $r['events_worked'] ?></td>
                                    <td class="text-info fw-bold">₹<?= number_format($salary, 2) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-5">No records found for the selected criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterYear = document.getElementById('filter_year');
    const filterMonth = document.getElementById('filter_month');
    const filterDate = document.getElementById('filter_date');

    if (filterYear && filterMonth && filterDate) {
        // When Exact Date changes
        filterDate.addEventListener('input', function() {
            if (this.value !== '') {
                filterYear.value = '';
                filterMonth.value = 'all';
            }
        });

        // When Year changes
        filterYear.addEventListener('change', function() {
            if (this.value !== '') {
                filterDate.value = '';
            }
        });

        // When Month changes
        filterMonth.addEventListener('change', function() {
            if (this.value !== 'all') {
                filterDate.value = '';
            }
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
