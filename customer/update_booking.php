<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$booking_id) { echo "<script>window.location='bookings.php';</script>"; exit; }

$bk = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name", "menu_packages.price", "menu_packages.image"),
    "bookings",
    "bookings.id=$booking_id AND customer_id=$user_id AND booking_status='approved'",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);

if(empty($bk)) {
    swalRedirect("Invalid booking or booking cannot be updated.", "bookings.php", "error", "Error");
    exit;
}

$booking = $bk[0];
$event_date_ts = strtotime($booking['event_date']);
$now_ts = time();
$days_diff = floor(($event_date_ts - $now_ts) / (60 * 60 * 24));

if($days_diff < 2) {
    swalRedirect("Bookings can only be updated 2 or more days prior to the event date.", "bookings.php", "success", "Success");
    exit;
}

// Get all packages
$packages = $dao->getData("*", "menu_packages", "status=1");

// Get blocked dates list
$blocked_dates_data = $dao->getData(array("blocked_date", "reason"), "blocked_dates");
$blocked_dates = array();
$blocked_dates_reasons = array();
if (!empty($blocked_dates_data)) {
    foreach ($blocked_dates_data as $bd) {
        $blocked_dates[] = $bd['blocked_date'];
        $blocked_dates_reasons[$bd['blocked_date']] = !empty($bd['reason']) ? $bd['reason'] : "unspecified maintenance or scheduling limits";
    }
}

// Get capacity limits
$capacity_data = $dao->getStaffCapacityData($booking_id);
foreach ($capacity_data['fully_blocked_dates'] as $bd) {
    if (!in_array($bd, $blocked_dates)) {
        $blocked_dates[] = $bd;
        $blocked_dates_reasons[$bd] = "Catering fully booked (no available staff capacity)";
    }
}
$date_capacities = $capacity_data['date_capacities'];
$default_capacity = $capacity_data['default_capacity'];

if(isset($_POST['btnUpdate'])) {
    $new_date = $_POST['event_date'];
    $new_time = $_POST['event_time'];
    $new_guests = intval($_POST['guest_count']);
    $new_days = intval($_POST['event_days']);
    $new_package_id = intval($_POST['package_id']);
    
    // Validate date and guest capacity
    $new_date_ts = strtotime($new_date);
    $new_days_diff = floor(($new_date_ts - $now_ts) / (60 * 60 * 24));
    
    $date_capacity = isset($date_capacities[$new_date]) ? $date_capacities[$new_date] : $default_capacity;
    
    if($new_days_diff < 2) {
        $msg = "<div class='alert alert-danger'>New event date must be at least 2 days from today.</div>";
    } elseif($new_date_ts > strtotime('+1 year')) {
        $msg = "<div class='alert alert-danger'>New event date cannot be more than 1 year from today.</div>";
    } elseif(in_array($new_date, $blocked_dates)) {
        $msg = "<div class='alert alert-danger'>The selected date (" . date('d M Y', strtotime($new_date)) . ") is unavailable for catering bookings.</div>";
    } else {
        $free_chefs = $capacity_data['total_staff']['chef'] - ($capacity_data['leaves_by_date'][$new_date]['chef'] ?? 0) - ($capacity_data['assigned_by_date'][$new_date]['chef'] ?? 0);
        $free_servers = $capacity_data['total_staff']['server'] - ($capacity_data['leaves_by_date'][$new_date]['server'] ?? 0) - ($capacity_data['assigned_by_date'][$new_date]['server'] ?? 0);
        $free_sm = $capacity_data['total_staff']['site_manager'] - ($capacity_data['leaves_by_date'][$new_date]['site_manager'] ?? 0) - ($capacity_data['assigned_by_date'][$new_date]['site_manager'] ?? 0);
        
        $req_chefs = ceil($new_guests / 350);
        $req_servers = ceil($new_guests / 250);
        $req_sm = $new_guests >= 1000 ? 2 : 1;
        
        if ($free_chefs < $req_chefs || $free_servers < $req_servers || $free_sm < $req_sm || $new_guests > 4000) {
            $max_capacity_on_date = min($free_chefs * 350, $free_servers * 250, $free_sm >= 2 ? 4000 : ($free_sm >= 1 ? 999 : 0));
            $max_capacity_on_date = min($max_capacity_on_date, 4000);
            
            $msg = "<div class='alert alert-danger'>The selected date (" . date('d M Y', strtotime($new_date)) . ") does not have enough staff available or exceeds the 4,000 guest limit. Maximum guests allowed on this date is " . $max_capacity_on_date . " guests.</div>";
        } else {
        // Calculate new total
        $pkg_data = $dao->getData("price", "menu_packages", "id=$new_package_id");
        $pkg_price = $pkg_data[0]['price'];
        
        $new_total = $pkg_price * $new_guests * $new_days;
        $old_total = $booking['total_amount'];
        $advance_paid = $booking['advance_amount'];
        
        $min_advance_required = $new_total * 0.50;
        
        if($new_total < $old_total) {
            // New amount is less. Adjust total, keep advance as is (or max at new total).
            $new_advance = min($advance_paid, $new_total);
            $dao->update(array(
                "event_date" => $new_date,
                "event_time" => $new_time,
                "guest_count" => $new_guests,
                "event_days" => $new_days,
                "package_id" => $new_package_id,
                "total_amount" => $new_total,
                "advance_amount" => $new_advance
            ), "bookings", "id=$booking_id");
            
            // Re-assign staff automatically since no extra money needed
            $dao->delete("booking_staff", "booking_id=$booking_id");
            
            // Auto allocation logic
            $req_chefs = ceil($new_guests / 100);
            $req_servers = $new_guests >= 1000 ? ceil($new_guests / 100) : ceil($new_guests / 50);
            $req_sm = $new_guests >= 1000 ? 3 : 1;
            $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$new_date' AND status='approved') 
                           AND id NOT IN (SELECT user_id FROM booking_staff JOIN bookings ON booking_staff.booking_id = bookings.id WHERE bookings.event_date = '$new_date' AND bookings.booking_status IN ('approved', 'completed') AND booking_staff.assignment_status = 'assigned')";
            
            $available_chefs = $dao->getData("id", "users", "role='chef' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_chefs");
            $available_servers = $dao->getData("id", "users", "role='server' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_servers");
            $available_sm = $dao->getData("id", "users", "role='site_manager' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_sm");
            
            if(is_array($available_chefs)) foreach($available_chefs as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"chef", "assignment_status"=>"assigned"), "booking_staff");
            if(is_array($available_servers)) foreach($available_servers as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"server", "assignment_status"=>"assigned"), "booking_staff");
            if(is_array($available_sm)) foreach($available_sm as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"site_manager", "assignment_status"=>"assigned"), "booking_staff");

            swalRedirect("Booking successfully updated! New Total: ₹".number_format($new_total,2).". Previous advance covers this.", "bookings.php", "success", "Success");
            exit;
        } else if($new_total > $old_total) {
            // New amount is higher. Need more money.
            $additional_required = $min_advance_required - $advance_paid;
            
            if($additional_required > 0) {
                // Update total but set payment status to pending to force them to pay difference
                $dao->update(array(
                    "event_date" => $new_date,
                    "event_time" => $new_time,
                    "guest_count" => $new_guests,
                    "event_days" => $new_days,
                    "package_id" => $new_package_id,
                    "total_amount" => $new_total,
                    "payment_status" => "pending" // Forces payment again
                ), "bookings", "id=$booking_id");
                
                // Remove existing staff because they haven't paid the extra advance yet
                $dao->delete("booking_staff", "booking_id=$booking_id");
                
                swalRedirect("Booking updated. New Total: ₹".number_format($new_total,2).". You must pay an additional ₹".number_format($additional_required,2)." advance to confirm this update and secure staff.", "bookings.php", "success", "Success");
                exit;
            } else {
                // Advance already paid is still >= 50% of the NEW total. No extra money needed.
                $dao->update(array(
                    "event_date" => $new_date,
                    "event_time" => $new_time,
                    "guest_count" => $new_guests,
                    "event_days" => $new_days,
                    "package_id" => $new_package_id,
                    "total_amount" => $new_total
                ), "bookings", "id=$booking_id");
                
                // Re-assign staff automatically
                $dao->delete("booking_staff", "booking_id=$booking_id");
                // Auto allocation logic
                $req_chefs = ceil($new_guests / 100);
                $req_servers = $new_guests >= 1000 ? ceil($new_guests / 100) : ceil($new_guests / 50);
                $req_sm = $new_guests >= 1000 ? 3 : 1;
                $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$new_date' AND status='approved') 
                               AND id NOT IN (SELECT user_id FROM booking_staff JOIN bookings ON booking_staff.booking_id = bookings.id WHERE bookings.event_date = '$new_date' AND bookings.booking_status IN ('approved', 'completed') AND booking_staff.assignment_status = 'assigned')";
                
                $available_chefs = $dao->getData("id", "users", "role='chef' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_chefs");
                $available_servers = $dao->getData("id", "users", "role='server' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_servers");
                $available_sm = $dao->getData("id", "users", "role='site_manager' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_sm");
                
                if(is_array($available_chefs)) foreach($available_chefs as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"chef", "assignment_status"=>"assigned"), "booking_staff");
                if(is_array($available_servers)) foreach($available_servers as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"server", "assignment_status"=>"assigned"), "booking_staff");
                if(is_array($available_sm)) foreach($available_sm as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"site_manager", "assignment_status"=>"assigned"), "booking_staff");

                swalRedirect("Booking successfully updated! No extra advance required.", "bookings.php", "success", "Success");
                exit;
            }
        } else {
            // Same total (e.g. just date change)
            $dao->update(array(
                "event_date" => $new_date,
                "event_time" => $new_time,
                "guest_count" => $new_guests,
                "event_days" => $new_days,
                "package_id" => $new_package_id
            ), "bookings", "id=$booking_id");
            
            // Re-assign staff automatically for the new date
            $dao->delete("booking_staff", "booking_id=$booking_id");
            // Auto allocation logic
            $req_chefs = ceil($new_guests / 100);
            $req_servers = $new_guests >= 1000 ? ceil($new_guests / 100) : ceil($new_guests / 50);
            $req_sm = $new_guests >= 1000 ? 3 : 1;
            $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$new_date' AND status='approved') 
                           AND id NOT IN (SELECT user_id FROM booking_staff JOIN bookings ON booking_staff.booking_id = bookings.id WHERE bookings.event_date = '$new_date' AND bookings.booking_status IN ('approved', 'completed') AND booking_staff.assignment_status = 'assigned')";
            
            $available_chefs = $dao->getData("id", "users", "role='chef' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_chefs");
            $available_servers = $dao->getData("id", "users", "role='server' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_servers");
            $available_sm = $dao->getData("id", "users", "role='site_manager' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_sm");
            
            if(is_array($available_chefs)) foreach($available_chefs as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"chef", "assignment_status"=>"assigned"), "booking_staff");
            if(is_array($available_servers)) foreach($available_servers as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"server", "assignment_status"=>"assigned"), "booking_staff");
            if(is_array($available_sm)) foreach($available_sm as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"site_manager", "assignment_status"=>"assigned"), "booking_staff");
            
            swalRedirect("Booking successfully updated!", "bookings.php", "success", "Success");
            exit;
        }
    }
}
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent text-gold border-0" style="color:var(--primary-gold);">
                    <h4 class="mb-0"><i class="fa fa-edit me-2"></i> Update Booking Details</h4>
                </div>
                <div class="card-body p-4">
                    <?= isset($msg) ? $msg : '' ?>
                    <form method="POST">
                        <div class="alert alert-info" style="background:rgba(212,175,55,0.1); border-color:var(--primary-gold); color:#fff;">
                            <strong>Current Total:</strong> ₹<?= number_format($booking['total_amount'],2) ?> <br>
                            <strong>Advance Paid:</strong> ₹<?= number_format($booking['advance_amount'],2) ?>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Event Date (Must be >= 2 days from now)</label>
                                <input type="text" name="event_date" id="event_date_picker" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" value="<?= $booking['event_date'] ?>" required placeholder="Select Date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Event Time</label>
                                <input type="time" name="event_time" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" value="<?= isset($booking['event_time']) ? htmlspecialchars($booking['event_time']) : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Guest Count</label>
                                <input type="number" name="guest_count" id="guest_count_input" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" value="<?= $booking['guest_count'] ?>" required min="50" onkeyup="checkCapacity()" onchange="checkCapacity()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Event Days</label>
                                <input type="number" name="event_days" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" value="<?= $booking['event_days'] ?>" required min="1">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted">Select Menu Package</label>
                            <select name="package_id" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" required>
                                <?php foreach($packages as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $booking['package_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['package_name']) ?> - ₹<?= number_format($p['price']) ?> / guest
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="bookings.php" class="btn btn-outline-light">Cancel</a>
                            <button type="submit" name="btnUpdate" class="btn btn-gold px-5 fw-bold" onclick="return confirm('If total is higher, you will need to pay additional advance. Proceed?');">Update Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const blockedDates = <?= json_encode($blocked_dates_reasons) ?>;
const dateCapacities = <?= json_encode($date_capacities) ?>;
const defaultCapacity = <?= intval($default_capacity) ?>;
const totalStaff = <?= json_encode($capacity_data['total_staff']) ?>;
const leavesByDate = <?= json_encode($capacity_data['leaves_by_date']) ?>;
const assignedByDate = <?= json_encode($capacity_data['assigned_by_date']) ?>;

function checkCapacity() {
    const dateInput = document.getElementById('event_date_picker');
    const guestInput = document.getElementById('guest_count_input');
    
    const dateStr = dateInput.value;
    const guests = parseInt(guestInput.value) || 0;
    
    if (dateStr) {
        // Calculate free staff on that date
        let freeChefs = totalStaff.chef || 0;
        let freeServers = totalStaff.server || 0;
        let freeSm = totalStaff.site_manager || 0;
        
        if (dateStr in leavesByDate) {
            freeChefs -= (leavesByDate[dateStr].chef || 0);
            freeServers -= (leavesByDate[dateStr].server || 0);
            freeSm -= (leavesByDate[dateStr].site_manager || 0);
        }
        if (dateStr in assignedByDate) {
            freeChefs -= (assignedByDate[dateStr].chef || 0);
            freeServers -= (assignedByDate[dateStr].server || 0);
            freeSm -= (assignedByDate[dateStr].site_manager || 0);
        }
        
        const reqChefs = Math.ceil(guests / 350);
        const reqServers = Math.ceil(guests / 250);
        const reqSm = guests >= 1000 ? 2 : 1;
        
        if (freeChefs < reqChefs || freeServers < reqServers || freeSm < reqSm || guests > 4000) {
            let maxCapacity = Math.min(freeChefs * 350, freeServers * 250);
            if (freeSm < 1) maxCapacity = 0;
            else if (freeSm < 2) maxCapacity = Math.min(maxCapacity, 999);
            
            maxCapacity = Math.min(maxCapacity, 4000);
            
            Swal.fire({
                title: 'Capacity Limit Exceeded',
                html: `<div class="text-center py-2">
                    <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="text-white">We have limited staff available on this date or the event size exceeds the 4,000 capacity limit.</p>
                    <p class="text-gold" style="color:var(--primary-gold)">Maximum supported guests: <strong>${maxCapacity}</strong></p>
                </div>`,
                icon: 'warning',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    confirmButton: 'swal2-confirm'
                }
            });
            guestInput.value = maxCapacity;
        }
    }
}

flatpickr('#event_date_picker', {
    minDate: new Date().fp_incr(2), // 2 days from now
    maxDate: "<?= date('Y-m-d', strtotime('+1 year')) ?>",
    dateFormat: "Y-m-d",
    theme: "dark",
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const y = dayElem.dateObj.getFullYear();
        const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
        const dateStr = `${y}-${m}-${d}`;
        if (dateStr in blockedDates) {
            dayElem.classList.add('blocked-date-custom');
        }
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (dateStr in blockedDates) {
            const reason = blockedDates[dateStr];
            const formattedDate = new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            Swal.fire({
                title: 'Catering Unavailable',
                html: `<div class="text-center py-2">
                    <i class="fa fa-sad-tear fa-3x text-gold mb-3" style="color:var(--primary-gold);"></i>
                    <p class="mb-2 text-white">We sincerely apologize, but Swizy catering is not operating on <strong>${formattedDate}</strong>.</p>
                    <p class="text-muted small"><strong>Reason:</strong> ${reason}</p>
                    <p class="mt-3 text-gold" style="color:var(--primary-gold);">Please select another date for your event.</p>
                </div>`,
                icon: 'info',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    confirmButton: 'swal2-confirm'
                }
            });
            instance.clear(); // Clear selection
        } else {
            checkCapacity();
        }
    }
});
</script>

<?php require_once('footer.php'); ?>
