<?php require_once('header.php'); 
$dao = new DataAccess();

// Get Stats
$total_bookings = $dao->count("id", "bookings", "1");
$pending_bookings = $dao->count("id", "bookings", "booking_status='pending'");
$total_revenue = 0;
$rev_data = $dao->query("select SUM(advance_amount) as total from bookings where payment_status IN ('advance_paid', 'fully_paid', 'refunded')");
if(!empty($rev_data) && isset($rev_data[0]['total'])) {
    $total_revenue = $rev_data[0]['total'];
}
$total_staff = $dao->count("id", "users", "role IN ('chef', 'server', 'site_manager')");

// Recent Bookings
$recent_bookings = $dao->getDataJoin(
    array("bookings.id", "users.name as customer_name", "bookings.event_type", "bookings.event_date", "bookings.booking_status"),
    "bookings",
    "1 ORDER BY bookings.id DESC LIMIT 5",
    array("users" => array("bookings.customer_id=users.id", "innerjoin"))
);

$chart_filter = isset($_GET['chart_filter']) ? $_GET['chart_filter'] : '6months';

$month_names_json = '[]';
$bookings_data_json = '[]';
$cancellations_data_json = '[]';
$revenue_data_json = '[]';

if ($chart_filter == 'weekly') {
    // Last 7 days, grouped by day
    $chart_days = array();
    for ($i = 6; $i >= 0; $i--) {
        $time = strtotime("-$i days");
        $date_str = date('Y-m-d', $time);
        $day_name = date('D d M', $time);
        $chart_days[$date_str] = array(
            'name' => $day_name,
            'bookings' => 0,
            'cancellations' => 0,
            'revenue' => 0
        );
    }
    
    $stats = $dao->query("
        SELECT 
            DATE(event_date) as event_date,
            COUNT(id) as total_bookings,
            SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as total_cancellations,
            SUM(CASE 
                WHEN payment_status = 'fully_paid' THEN total_amount 
                WHEN payment_status = 'advance_paid' THEN advance_amount 
                ELSE 0 
            END) as revenue
        FROM bookings
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(event_date)
    ");
    
    if (is_array($stats)) {
        foreach ($stats as $stat) {
            $d = $stat['event_date'];
            if (isset($chart_days[$d])) {
                $chart_days[$d]['bookings'] = intval($stat['total_bookings']);
                $chart_days[$d]['cancellations'] = intval($stat['total_cancellations']);
                $chart_days[$d]['revenue'] = floatval($stat['revenue']);
            }
        }
    }
    
    $month_names_json = json_encode(array_values(array_column($chart_days, 'name')));
    $bookings_data_json = json_encode(array_values(array_column($chart_days, 'bookings')));
    $cancellations_data_json = json_encode(array_values(array_column($chart_days, 'cancellations')));
    $revenue_data_json = json_encode(array_values(array_column($chart_days, 'revenue')));
}
elseif ($chart_filter == 'monthly') {
    // Last 30 days, grouped by date
    $chart_days = array();
    for ($i = 29; $i >= 0; $i--) {
        $time = strtotime("-$i days");
        $date_str = date('Y-m-d', $time);
        $day_name = date('d M', $time);
        $chart_days[$date_str] = array(
            'name' => $day_name,
            'bookings' => 0,
            'cancellations' => 0,
            'revenue' => 0
        );
    }
    
    $stats = $dao->query("
        SELECT 
            DATE(event_date) as event_date,
            COUNT(id) as total_bookings,
            SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as total_cancellations,
            SUM(CASE 
                WHEN payment_status = 'fully_paid' THEN total_amount 
                WHEN payment_status = 'advance_paid' THEN advance_amount 
                ELSE 0 
            END) as revenue
        FROM bookings
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
        GROUP BY DATE(event_date)
    ");
    
    if (is_array($stats)) {
        foreach ($stats as $stat) {
            $d = $stat['event_date'];
            if (isset($chart_days[$d])) {
                $chart_days[$d]['bookings'] = intval($stat['total_bookings']);
                $chart_days[$d]['cancellations'] = intval($stat['total_cancellations']);
                $chart_days[$d]['revenue'] = floatval($stat['revenue']);
            }
        }
    }
    
    $month_names_json = json_encode(array_values(array_column($chart_days, 'name')));
    $bookings_data_json = json_encode(array_values(array_column($chart_days, 'bookings')));
    $cancellations_data_json = json_encode(array_values(array_column($chart_days, 'cancellations')));
    $revenue_data_json = json_encode(array_values(array_column($chart_days, 'revenue')));
}
elseif ($chart_filter == 'yearly') {
    // Last 12 months, grouped by month
    $chart_months = array();
    for ($i = 11; $i >= 0; $i--) {
        $time = strtotime("-$i months");
        $month_num = intval(date('m', $time));
        $month_name = date('M Y', $time);
        $chart_months[$month_num] = array(
            'name' => $month_name,
            'bookings' => 0,
            'cancellations' => 0,
            'revenue' => 0
        );
    }
    
    $stats = $dao->query("
        SELECT 
            MONTH(event_date) as month_num,
            COUNT(id) as total_bookings,
            SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as total_cancellations,
            SUM(CASE 
                WHEN payment_status = 'fully_paid' THEN total_amount 
                WHEN payment_status = 'advance_paid' THEN advance_amount 
                ELSE 0 
            END) as revenue
        FROM bookings
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY MONTH(event_date)
    ");
    
    if (is_array($stats)) {
        foreach ($stats as $stat) {
            $m = intval($stat['month_num']);
            if (isset($chart_months[$m])) {
                $chart_months[$m]['bookings'] = intval($stat['total_bookings']);
                $chart_months[$m]['cancellations'] = intval($stat['total_cancellations']);
                $chart_months[$m]['revenue'] = floatval($stat['revenue']);
            }
        }
    }
    
    $month_names_json = json_encode(array_values(array_column($chart_months, 'name')));
    $bookings_data_json = json_encode(array_values(array_column($chart_months, 'bookings')));
    $cancellations_data_json = json_encode(array_values(array_column($chart_months, 'cancellations')));
    $revenue_data_json = json_encode(array_values(array_column($chart_months, 'revenue')));
}
elseif ($chart_filter == 'custom') {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    // Group by date for custom range
    $chart_days = array();
    $start_time = strtotime($start_date);
    $end_time = strtotime($end_date);
    
    // Limit range to max 90 days to avoid clogging the chart
    $diff_days = round(($end_time - $start_time) / (60 * 60 * 24));
    if ($diff_days < 0 || $diff_days > 90) {
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $start_time = strtotime($start_date);
        $diff_days = 30;
    }
    
    for ($i = 0; $i <= $diff_days; $i++) {
        $time = strtotime("+$i days", $start_time);
        $date_str = date('Y-m-d', $time);
        $day_name = date('d M', $time);
        $chart_days[$date_str] = array(
            'name' => $day_name,
            'bookings' => 0,
            'cancellations' => 0,
            'revenue' => 0
        );
    }
    
    $stats = $dao->query("
        SELECT 
            DATE(event_date) as event_date,
            COUNT(id) as total_bookings,
            SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as total_cancellations,
            SUM(CASE 
                WHEN payment_status = 'fully_paid' THEN total_amount 
                WHEN payment_status = 'advance_paid' THEN advance_amount 
                ELSE 0 
            END) as revenue
        FROM bookings
        WHERE event_date BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE(event_date)
    ");
    
    if (is_array($stats)) {
        foreach ($stats as $stat) {
            $d = $stat['event_date'];
            if (isset($chart_days[$d])) {
                $chart_days[$d]['bookings'] = intval($stat['total_bookings']);
                $chart_days[$d]['cancellations'] = intval($stat['total_cancellations']);
                $chart_days[$d]['revenue'] = floatval($stat['revenue']);
            }
        }
    }
    
    $month_names_json = json_encode(array_values(array_column($chart_days, 'name')));
    $bookings_data_json = json_encode(array_values(array_column($chart_days, 'bookings')));
    $cancellations_data_json = json_encode(array_values(array_column($chart_days, 'cancellations')));
    $revenue_data_json = json_encode(array_values(array_column($chart_days, 'revenue')));
}
else {
    // Default: Last 6 months
    $chart_months = array();
    for ($i = 5; $i >= 0; $i--) {
        $time = strtotime("-$i months");
        $month_num = intval(date('m', $time));
        $month_name = date('M', $time);
        $chart_months[$month_num] = array(
            'name' => $month_name,
            'bookings' => 0,
            'cancellations' => 0,
            'revenue' => 0
        );
    }
    
    $stats = $dao->query("
        SELECT 
            MONTH(event_date) as month_num,
            COUNT(id) as total_bookings,
            SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as total_cancellations,
            SUM(CASE 
                WHEN payment_status = 'fully_paid' THEN total_amount 
                WHEN payment_status = 'advance_paid' THEN advance_amount 
                ELSE 0 
            END) as revenue
        FROM bookings
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY MONTH(event_date)
    ");
    
    if (is_array($stats)) {
        foreach ($stats as $stat) {
            $m = intval($stat['month_num']);
            if (isset($chart_months[$m])) {
                $chart_months[$m]['bookings'] = intval($stat['total_bookings']);
                $chart_months[$m]['cancellations'] = intval($stat['total_cancellations']);
                $chart_months[$m]['revenue'] = floatval($stat['revenue']);
            }
        }
    }
    
    $month_names_json = json_encode(array_values(array_column($chart_months, 'name')));
    $bookings_data_json = json_encode(array_values(array_column($chart_months, 'bookings')));
    $cancellations_data_json = json_encode(array_values(array_column($chart_months, 'cancellations')));
    $revenue_data_json = json_encode(array_values(array_column($chart_months, 'revenue')));
}

?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3 text-center border-start border-warning border-4">
                <h5 class="text-muted mb-2">Total Bookings</h5>
                <h3 class="text-gold" style="color: var(--primary-gold);"><?= $total_bookings ? $total_bookings : 0 ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center border-start border-info border-4">
                <h5 class="text-muted mb-2">Pending Approvals</h5>
                <h3 class="text-info"><?= $pending_bookings ? $pending_bookings : 0 ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center border-start border-success border-4">
                <h5 class="text-muted mb-2">Revenue (Advance)</h5>
                <h3 class="text-success">₹<?= number_format($total_revenue, 2) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center border-start border-primary border-4">
                <h5 class="text-muted mb-2">Total Staff</h5>
                <h3 class="text-primary"><?= $total_staff ? $total_staff : 0 ?></h3>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent text-gold border-0 d-flex flex-wrap justify-content-between align-items-center gap-2" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-chart-line me-2"></i>Performance Analytics</h5>
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap" id="chartFilterForm">
                        <select name="chart_filter" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: auto; padding-right: 25px;" onchange="toggleCustomDates(this.value); if(this.value !== 'custom') this.form.submit();">
                            <option value="weekly" <?= $chart_filter=='weekly'?'selected':'' ?>>Weekly (Last 7 Days)</option>
                            <option value="monthly" <?= $chart_filter=='monthly'?'selected':'' ?>>Monthly (Last 30 Days)</option>
                            <option value="6months" <?= $chart_filter=='6months'?'selected':'' ?>>6 Months</option>
                            <option value="yearly" <?= $chart_filter=='yearly'?'selected':'' ?>>Yearly (Last 12 Months)</option>
                            <option value="custom" <?= $chart_filter=='custom'?'selected':'' ?>>Custom Date Range</option>
                        </select>
                        <div id="customDateInputs" class="d-flex align-items-center gap-2 <?= $chart_filter=='custom'?'':'d-none' ?>">
                            <input type="date" name="start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-d', strtotime('-30 days')) ?>">
                            <span class="text-muted small">to</span>
                            <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-d') ?>">
                            <button type="submit" class="btn btn-sm btn-gold"><i class="fa fa-sync-alt"></i></button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div id="analyticsChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Bookings</h5>
                    <a href="bookings.php" class="btn btn-sm btn-outline-gold" style="color: var(--primary-gold); border-color: var(--primary-gold);">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($recent_bookings)): foreach($recent_bookings as $bk): ?>
                                <tr>
                                    <td>#<?= $bk['id'] ?></td>
                                    <td><?= htmlspecialchars($bk['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($bk['event_type']) ?></td>
                                    <td><?= date('d M Y', strtotime($bk['event_date'])) ?></td>
                                    <td>
                                        <?php if($bk['booking_status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif($bk['booking_status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($bk['booking_status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No recent bookings found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="menu_add.php" class="btn btn-gold text-start"><i class="fa fa-plus-circle me-2"></i> Add Menu Package</a>
                        <a href="staff_add.php" class="btn btn-gold text-start"><i class="fa fa-user-plus me-2"></i> Register Staff</a>
                        <a href="bookings.php" class="btn btn-gold text-start"><i class="fa fa-calendar-check me-2"></i> Manage Bookings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        series: [{
            name: 'Bookings',
            type: 'area',
            data: <?= $bookings_data_json ?>
        }, {
            name: 'Cancellations',
            type: 'line',
            data: <?= $cancellations_data_json ?>
        }, {
            name: 'Revenue',
            type: 'area',
            data: <?= $revenue_data_json ?>
        }],
        chart: {
            height: 350,
            type: 'line',
            stacked: false,
            background: 'transparent',
            foreColor: '#aaaaaa',
            toolbar: {
                show: false
            }
        },
        colors: ['#d4af37', '#ff4d4d', '#2ecc71'], // Gold, Red, Green
        stroke: {
            width: [3, 2, 3],
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        labels: <?= $month_names_json ?>,
        markers: {
            size: 4,
            colors: ['#d4af37', '#ff4d4d', '#2ecc71'],
            strokeColors: '#1e1e1e',
            strokeWidth: 2
        },
        yaxis: [
            {
                title: {
                    text: "Bookings & Cancellations",
                    style: {
                        color: '#d4af37',
                        fontWeight: 500
                    }
                },
                labels: {
                    style: {
                        colors: '#d4af37'
                    }
                }
            },
            {
                opposite: true,
                title: {
                    text: "Revenue (₹)",
                    style: {
                        color: '#2ecc71',
                        fontWeight: 500
                    }
                },
                labels: {
                    style: {
                        colors: '#2ecc71'
                    },
                    formatter: function (value) {
                        return "₹" + value.toLocaleString('en-IN');
                    }
                }
            }
        ],
        tooltip: {
            theme: 'dark',
            shared: true,
            intersect: false,
            y: {
                formatter: function (y, { seriesIndex }) {
                    if (typeof y !== "undefined") {
                        if (seriesIndex === 2) {
                            return "₹" + y.toLocaleString('en-IN', {minimumFractionDigits: 2});
                        }
                        return y + " events";
                    }
                    return y;
                }
            }
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.05)'
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            labels: {
                colors: '#ffffff'
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#analyticsChart"), options);
    chart.render();
});

function toggleCustomDates(value) {
    const customDiv = document.getElementById('customDateInputs');
    if (value === 'custom') {
        customDiv.classList.remove('d-none');
    } else {
        customDiv.classList.add('d-none');
    }
}
</script>

<?php require_once('footer.php'); ?>
