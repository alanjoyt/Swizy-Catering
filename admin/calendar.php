<?php 
require_once('header.php'); 
$dao = new DataAccess();

// Process actions (Block/Unblock)
$msg = "";
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'block' && isset($_POST['blocked_date'])) {
        $blocked_date = $_POST['blocked_date'];
        $reason = $dao->escape(trim($_POST['reason']));
        
        // Validation: date format and unique
        if (!empty($blocked_date)) {
            $existing = $dao->getData("*", "blocked_dates", "blocked_date = '$blocked_date'");
            if (empty($existing)) {
                $dao->insert(array(
                    "blocked_date" => $blocked_date,
                    "reason" => $reason
                ), "blocked_dates");
                $msg = "<div class='alert alert-success'>Date <strong>$blocked_date</strong> blocked successfully.</div>";
            } else {
                $msg = "<div class='alert alert-warning'>Date <strong>$blocked_date</strong> is already blocked.</div>";
            }
        }
    } elseif ($_GET['action'] == 'unblock' && isset($_GET['date'])) {
        $date = $_GET['date'];
        $dao->delete("blocked_dates", "blocked_date = '$date'");
        $msg = "<div class='alert alert-success'>Date <strong>$date</strong> unblocked successfully.</div>";
    }
}

// Current or Selected Month & Year
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

// Date Calculations
$first_day_of_month = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$first_day_of_week = date('w', strtotime($first_day_of_month)); // 0 = Sunday, 6 = Saturday
$days_in_month = date('t', strtotime($first_day_of_month));

// Fetch bookings for the selected month
$bookings_raw = $dao->getDataJoin(
    array("bookings.id", "bookings.customer_id", "users.name as customer_name", "bookings.event_type", "bookings.event_date", "bookings.event_time", "bookings.guest_count", "bookings.booking_status", "bookings.total_amount"),
    "bookings",
    "MONTH(bookings.event_date) = $month AND YEAR(bookings.event_date) = $year AND bookings.booking_status != 'cancelled'",
    array("users" => array("bookings.customer_id = users.id", "innerjoin"))
);

// Map bookings to day of month
$bookings_by_day = array();
if (!empty($bookings_raw)) {
    foreach ($bookings_raw as $bk) {
        $day = intval(date('d', strtotime($bk['event_date'])));
        $bookings_by_day[$day][] = $bk;
    }
}

// Fetch blocked dates for the selected month
$blocked_raw = $dao->getData("*", "blocked_dates", "MONTH(blocked_date) = $month AND YEAR(blocked_date) = $year");
$blocked_by_day = array();
if (!empty($blocked_raw)) {
    foreach ($blocked_raw as $bd) {
        $day = intval(date('d', strtotime($bd['blocked_date'])));
        $blocked_by_day[$day] = $bd;
    }
}

// Fetch all blocked dates for sidebar list
$all_blocked = $dao->getData("*", "blocked_dates", "blocked_date >= CURDATE() ORDER BY blocked_date ASC");
?>

<style>
    /* Premium CSS Grid Calendar */
    .calendar-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.15);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        background: rgba(10, 10, 10, 0.6);
    }
    
    .calendar-grid-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: rgba(212, 175, 55, 0.05);
        border-bottom: 1.5px solid rgba(212, 175, 55, 0.2);
    }
    
    .calendar-header-cell {
        padding: 14px 0;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1.5px;
        font-weight: 600;
        color: var(--primary-gold);
        text-align: center;
        border-right: 1px solid rgba(212, 175, 55, 0.1);
    }
    .calendar-header-cell:last-child {
        border-right: none;
    }
    
    .calendar-grid-body {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }
    
    .calendar-day-cell {
        min-height: 125px;
        padding: 12px;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }
    
    .calendar-day-cell:nth-child(7n) {
        border-right: none;
    }
    
    .calendar-day-cell:hover {
        background: rgba(212, 175, 55, 0.02);
    }
    
    .calendar-day-cell.empty {
        background: rgba(255, 255, 255, 0.01);
    }
    
    .calendar-day-cell.today {
        background: rgba(212, 175, 55, 0.06);
    }
    
    .calendar-day-cell.blocked {
        background: rgba(220, 53, 69, 0.04);
    }
    
    .calendar-day-cell.blocked:hover {
        background: rgba(220, 53, 69, 0.07);
    }
    
    .day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    
    .day-num {
        font-size: 1rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7);
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: 0.3s;
    }
    
    .calendar-day-cell.today .day-num {
        background: var(--primary-gold);
        color: #000;
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
    }
    
    .calendar-day-cell:hover .day-num {
        color: #fff;
    }
    
    /* Premium event badge styling */
    .event-item {
        font-size: 0.72rem;
        padding: 5px 8px;
        border-radius: 6px;
        margin-bottom: 5px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: 1px solid transparent;
        display: block;
    }
    
    .event-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }
    
    .event-item.pending {
        background: rgba(255, 193, 7, 0.12) !important;
        border-color: rgba(255, 193, 7, 0.3) !important;
        color: #ffc107 !important;
    }
    
    .event-item.approved {
        background: rgba(40, 167, 69, 0.12) !important;
        border-color: rgba(40, 167, 69, 0.3) !important;
        color: #81c784 !important;
    }
    
    .event-item.completed {
        background: rgba(23, 162, 184, 0.12) !important;
        border-color: rgba(23, 162, 184, 0.3) !important;
        color: #4dd0e1 !important;
    }
    
    .quick-action-link {
        font-size: 0.7rem;
        text-decoration: none;
        color: var(--text-muted);
        opacity: 0;
        transition: 0.3s;
    }
    
    .calendar-day-cell:hover .quick-action-link {
        opacity: 1;
    }
    
    .quick-action-link:hover {
        color: var(--primary-gold) !important;
    }
    
    .quick-action-link.unblock-link {
        color: var(--primary-gold) !important;
        opacity: 1; /* Always show unblock */
    }
    
    .quick-action-link.unblock-link:hover {
        color: #ff5252 !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <?= $msg ?>
        </div>
    </div>

    <div class="row">
        <!-- Calendar Grid -->
        <div class="col-md-9 mb-4">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-gold font-monospace" style="color:var(--primary-gold);"><i class="fa fa-calendar-alt me-2"></i> Event Calendar</h5>
                    
                    <div class="d-flex align-items-center gap-2">
                        <a href="calendar.php?month=<?= $month - 1 ?>&year=<?= $year ?>" class="btn btn-sm btn-outline-gold"><i class="fa fa-chevron-left"></i></a>
                        <span class="fs-5 fw-bold brand-font px-3 text-white"><?= date('F Y', strtotime($first_day_of_month)) ?></span>
                        <a href="calendar.php?month=<?= $month + 1 ?>&year=<?= $year ?>" class="btn btn-sm btn-outline-gold"><i class="fa fa-chevron-right"></i></a>
                    </div>
                </div>
                
                <div class="card-body p-3">
                    <div class="calendar-container">
                        <!-- Calendar Header (Days of week) -->
                        <div class="calendar-grid-header">
                            <div class="calendar-header-cell">Sun</div>
                            <div class="calendar-header-cell">Mon</div>
                            <div class="calendar-header-cell">Tue</div>
                            <div class="calendar-header-cell">Wed</div>
                            <div class="calendar-header-cell">Thu</div>
                            <div class="calendar-header-cell">Fri</div>
                            <div class="calendar-header-cell">Sat</div>
                        </div>
                        
                        <!-- Calendar Body Grid -->
                        <div class="calendar-grid-body">
                            <?php 
                            // Empty cells before start day of month
                            for ($i = 0; $i < $first_day_of_week; $i++) {
                                echo '<div class="calendar-day-cell empty"></div>';
                            }
                            
                            // Render days of the month
                            for ($day = 1; $day <= $days_in_month; $day++) {
                                $current_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                                $is_blocked = isset($blocked_by_day[$day]);
                                $has_bookings = isset($bookings_by_day[$day]);
                                $is_today = ($current_date == date('Y-m-d'));
                                
                                $cell_classes = "calendar-day-cell";
                                if ($is_blocked) $cell_classes .= " blocked";
                                if ($is_today) $cell_classes .= " today";
                                
                                echo "<div class='$cell_classes'>";
                                
                                // Day Header
                                echo '<div class="day-header">';
                                echo "<span class='day-num'>$day</span>";
                                if ($is_blocked) {
                                    $reason_tooltip = htmlspecialchars($blocked_by_day[$day]['reason']);
                                    echo "<span class='badge bg-danger p-1' title='Blocked: $reason_tooltip' style='font-size:0.65rem;'><i class='fa fa-lock'></i></span>";
                                }
                                echo '</div>';
                                
                                // Bookings list inside the day cell
                                echo '<div class="flex-grow-1 overflow-auto py-1" style="max-height: 75px;">';
                                if ($has_bookings) {
                                    foreach ($bookings_by_day[$day] as $bk) {
                                        $status_class = 'pending';
                                        if ($bk['booking_status'] == 'approved') $status_class = 'approved';
                                        elseif ($bk['booking_status'] == 'completed') $status_class = 'completed';
                                        
                                        $bk_json = json_encode($bk);
                                        echo "<div class='event-item $status_class' title='#{$bk['id']} - Click for details' onclick='showBookingDetails(" . htmlspecialchars($bk_json, ENT_QUOTES) . ")'>";
                                        echo "<i class='fa fa-circle me-1' style='font-size:0.4rem;'></i> #" . $bk['id'] . " " . htmlspecialchars($bk['event_type']);
                                        echo "</div>";
                                    }
                                }
                                echo '</div>';
                                
                                // Quick Action Button
                                echo '<div class="text-end">';
                                if ($is_blocked) {
                                    echo "<a href='calendar.php?month=$month&year=$year&action=unblock&date=$current_date' class='quick-action-link unblock-link'><i class='fa fa-lock-open me-1'></i>Unblock</a>";
                                } else {
                                    echo "<a href='javascript:void(0);' onclick='openQuickBlock(\"$current_date\")' class='quick-action-link'><i class='fa fa-ban me-1'></i>Block</a>";
                                }
                                echo '</div>';
                                
                                echo '</div>';
                            }
                            
                            // Empty cells after end of month
                            $total_cells_rendered = $first_day_of_week + $days_in_month;
                            $remaining_cells = 7 - ($total_cells_rendered % 7);
                            if ($remaining_cells < 7) {
                                for ($i = 0; $i < $remaining_cells; $i++) {
                                    echo '<div class="calendar-day-cell empty"></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar: Block Date Form & List -->
        <div class="col-md-3">
            <!-- Form Card -->
            <div class="card shadow border-0 mb-4" style="background:var(--card-bg);">
                <div class="card-header bg-transparent border-bottom border-secondary py-3 text-gold" style="color:var(--primary-gold);">
                    <h6 class="mb-0 fw-bold"><i class="fa fa-ban me-2"></i> Block Specific Date</h6>
                </div>
                <div class="card-body">
                    <form method="POST" id="blockForm" action="calendar.php?month=<?= $month ?>&year=<?= $year ?>&action=block">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Select Date *</label>
                            <input type="date" name="blocked_date" id="block_date_input" class="form-control" style="background:rgba(0,0,0,0.25);" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Reason / Note (Optional)</label>
                            <textarea name="reason" rows="2" class="form-control" style="background:rgba(0,0,0,0.25);" placeholder="e.g. Maintenance, Private Event"></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold btn-sm w-100 fw-bold"><i class="fa fa-lock me-1"></i> Block Selected Date</button>
                    </form>
                </div>
            </div>
            
            <!-- Blocked List Card -->
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent border-bottom border-secondary py-3 text-gold" style="color:var(--primary-gold);">
                    <h6 class="mb-0 fw-bold"><i class="fa fa-list me-2"></i> Blocked Dates (Upcoming)</h6>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <?php if (!empty($all_blocked)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($all_blocked as $bd): ?>
                                <li class="list-group-item bg-transparent text-white border-bottom border-secondary py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-danger"><i class="fa fa-calendar-times me-2"></i><?= date('d M Y', strtotime($bd['blocked_date'])) ?></strong>
                                        <?php if (!empty($bd['reason'])): ?>
                                            <small class="text-muted d-block mt-1 font-italic"><?= htmlspecialchars($bd['reason']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="calendar.php?month=<?= $month ?>&year=<?= $year ?>&action=unblock&date=<?= $bd['blocked_date'] ?>" class="btn btn-sm btn-outline-danger" title="Unblock Date"><i class="fa fa-lock-open"></i></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">No upcoming blocked dates.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openQuickBlock(date) {
    document.getElementById('block_date_input').value = date;
    document.getElementById('block_date_input').scrollIntoView({ behavior: 'smooth' });
}

function showBookingDetails(bk) {
    let detailsHtml = `
        <div class="text-start">
            <p><strong>Customer Name:</strong> ${bk.customer_name}</p>
            <p><strong>Event Date:</strong> ${formatDateStr(bk.event_date)} ${bk.event_time ? 'at ' + bk.event_time : ''}</p>
            <p><strong>Event Type:</strong> ${bk.event_type}</p>
            <p><strong>Estimated Guests:</strong> ${bk.guest_count} guests</p>
            <p><strong>Total Amount:</strong> ₹${parseFloat(bk.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
            <p><strong>Status:</strong> <span class="badge ${getStatusBadge(bk.booking_status)}">${bk.booking_status.toUpperCase()}</span></p>
        </div>
    `;

    Swal.fire({
        title: `Booking Details (ID: #${bk.id})`,
        html: detailsHtml,
        icon: 'info',
        confirmButtonText: 'Close',
        showCancelButton: true,
        cancelButtonText: 'Assign Staff',
        cancelButtonColor: '#d33',
        customClass: {
            popup: 'swal2-popup',
            title: 'swal2-title',
            confirmButton: 'swal2-confirm'
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = `staff_assign.php?id=${bk.id}`;
        }
    });
}

function formatDateStr(dateStr) {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-IN', options);
}

function getStatusBadge(status) {
    if (status === 'pending') return 'bg-warning text-dark';
    if (status === 'approved') return 'bg-success';
    if (status === 'completed') return 'bg-info text-dark';
    return 'bg-secondary';
}
</script>

<?php require_once('footer.php'); ?>
