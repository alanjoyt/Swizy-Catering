<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

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
?>

<div class="container py-4">
    <div class="card shadow border-0 mb-4" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-gold border-0" style="color:var(--primary-gold);">
            <h5 class="mb-0"><i class="fa fa-file-invoice-dollar me-2"></i> My Booking Reports</h5>
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
                    <label class="form-label text-muted">Year</label>
                    <select name="year" id="filter_year" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);">
                        <option value="">-- Select Year --</option>
                        <?php for($i=date('Y')-2; $i<=date('Y')+2; $i++): ?>
                            <option value="<?= $i ?>" <?= $filter_year==$i?'selected':'' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted">Month</label>
                    <select name="month" id="filter_month" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);">
                        <option value="all" <?= $filter_month=='all'?'selected':'' ?>>All Months</option>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $filter_month==$i?'selected':'' ?>><?= date('F', mktime(0,0,0,$i,10)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Or Exact Date</label>
                    <input type="date" name="exact_date" id="filter_date" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" value="<?= $filter_date ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold w-100"><i class="fa fa-filter"></i> View</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow border-0" style="background:var(--card-bg);">
        <div class="card-header bg-transparent text-white border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">Found <?= is_array($results) ? count($results) : 0 ?> Events</h6>
            <div class="d-flex align-items-center gap-3">
                <strong style="color:var(--primary-gold);">Total Spent (Completed): ₹<?= number_format($total_spent, 2) ?></strong>
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
                            <th class="ps-4">Event Date</th>
                            <th>Event Type</th>
                            <th>Menu</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>  
                        <?php if(!empty($results)): foreach($results as $r): ?>
                            <tr>
                                <td class="ps-4"><strong><?= date('d M Y', strtotime($r['event_date'])) ?></strong></td>
                                <td><?= htmlspecialchars($r['event_type']) ?></td>
                                <td><small class="text-muted"><?= $r['is_custom'] ? 'Custom Menu' : htmlspecialchars($r['package_name']) ?></small></td>
                                <td><span class="badge bg-secondary"><?= ucfirst($r['booking_status']) ?></span></td>
                                <td>₹<?= number_format($r['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-5">No bookings found in this period.</td></tr>
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
