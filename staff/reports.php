<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Rates
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

?>

<div class="container-fluid py-4">
    <div class="card shadow border-0 mb-4" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-gold border-0" style="color:var(--primary-gold);">
            <h5 class="mb-0"><i class="fa fa-chart-line me-2"></i> Staff Reports</h5>
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
                        <option value="income" <?= $report_type=='income'?'selected':'' ?>>Income Report</option>
                        <option value="leave" <?= $report_type=='leave'?'selected':'' ?>>My Leave History</option>
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
            <h6 class="mb-0 text-muted">Found <?= is_array($results) ? count($results) : 0 ?> Records</h6>
            <div class="d-flex align-items-center gap-3">
                <?php if($report_type == 'income'): ?>
                    <strong class="text-success">Total Income: ₹<?= number_format($total_income, 2) ?> (@ ₹<?= number_format($my_rate) ?> / event)</strong>
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
                            <?php if($report_type == 'income'): ?>
                                <th class="ps-4">Date Worked</th><th>Event Type</th><th>Location</th><th>Earnings</th>
                            <?php elseif($report_type == 'leave'): ?>
                                <th class="ps-4">Leave Date</th><th>Reason</th><th>Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($results)): foreach($results as $r): ?>
                            <tr>
                                <?php if($report_type == 'income'): ?>
                                    <td class="ps-4"><strong><?= date('d M Y', strtotime($r['event_date'])) ?></strong></td>
                                    <td><?= htmlspecialchars($r['event_type']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($r['location']) ?></small></td>
                                    <td class="text-success">₹<?= number_format($my_rate, 2) ?></td>
                                <?php elseif($report_type == 'leave'): ?>
                                    <td class="ps-4"><strong><?= date('d M Y', strtotime($r['leave_date'])) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($r['reason']) ?: 'N/A' ?></small></td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">No records found.</td></tr>
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
