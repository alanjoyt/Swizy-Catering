<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

// Get packages for cards
$packages = $dao->getData("*", "menu_packages", "status=1");

// Get all items for custom selection
$custom_items = $dao->getData("*", "items", "status=1 ORDER BY category, name");

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
$capacity_data = $dao->getStaffCapacityData();
foreach ($capacity_data['fully_blocked_dates'] as $bd) {
    if (!in_array($bd, $blocked_dates)) {
        $blocked_dates[] = $bd;
        $blocked_dates_reasons[$bd] = "Catering fully booked (no available staff capacity)";
    }
}
$date_capacities = $capacity_data['date_capacities'];
$default_capacity = $capacity_data['default_capacity'];

$errors = array();
if(isset($_POST['btnProceed'])) {
    $event_type = trim($_POST['event_type']);
    $location = trim($_POST['location']);
    $special_notes = trim($_POST['special_notes']);
    $days = $_POST['days']; // Array of day data

    if(empty($event_type) || empty($location) || empty($days)) {
        $errors[] = "Please fill all required global fields and add at least one event day.";
    }

    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $booking_ids = [];
    $total_grand = 0;

    if(empty($errors)) {
        // Validate and process each day
        foreach($days as $i => $day) {
            $event_date = $day['date'];
            $event_time = isset($day['time']) ? trim($day['time']) : '';
            $guest_count = intval($day['guests']);
            $menu_mode = $day['menu_mode']; // 'package' or 'custom'
            
            if(empty($event_date) || empty($event_time) || $guest_count < 10) {
                $errors[] = "Day " . ($i+1) . ": Invalid date, time, or guest count (min 10).";
                break;
            }
            if(strtotime($event_date) < strtotime(date('Y-m-d', strtotime('+3 days')))) {
                $errors[] = "Day " . ($i+1) . ": Events must be booked at least 3 days in advance.";
                break;
            }
            if(strtotime($event_date) > strtotime(date('Y-m-d', strtotime('+1 year')))) {
                $errors[] = "Day " . ($i+1) . ": Events cannot be booked more than 1 year in advance.";
                break;
            }
            if(in_array($event_date, $blocked_dates)) {
                $errors[] = "Day " . ($i+1) . ": Date " . date('d M Y', strtotime($event_date)) . " is unavailable for booking.";
                break;
            }
            
            $free_chefs = $capacity_data['total_staff']['chef'] - ($capacity_data['leaves_by_date'][$event_date]['chef'] ?? 0) - ($capacity_data['assigned_by_date'][$event_date]['chef'] ?? 0);
            $free_servers = $capacity_data['total_staff']['server'] - ($capacity_data['leaves_by_date'][$event_date]['server'] ?? 0) - ($capacity_data['assigned_by_date'][$event_date]['server'] ?? 0);
            $free_sm = $capacity_data['total_staff']['site_manager'] - ($capacity_data['leaves_by_date'][$event_date]['site_manager'] ?? 0) - ($capacity_data['assigned_by_date'][$event_date]['site_manager'] ?? 0);
            
            $req_chefs = ceil($guest_count / 350);
            $req_servers = ceil($guest_count / 250);
            $req_sm = $guest_count >= 1000 ? 2 : 1;
            
            if ($free_chefs < $req_chefs || $free_servers < $req_servers || $free_sm < $req_sm || $guest_count > 4000) {
                $max_capacity_on_date = min($free_chefs * 350, $free_servers * 250, $free_sm >= 2 ? 4000 : ($free_sm >= 1 ? 999 : 0));
                $max_capacity_on_date = min($max_capacity_on_date, 4000);
                
                $errors[] = "Day " . ($i+1) . ": Selected date " . date('d M Y', strtotime($event_date)) . " does not have enough staff available or exceeds the 4,000 guest limit. Maximum guests allowed on this date is " . $max_capacity_on_date . " guests.";
                break;
            }

            $price_per_person = 0;
            $is_custom = 0;
            $package_id = "NULL";
            $custom_items_json = "NULL";

            if($menu_mode == 'custom') {
                $is_custom = 1;
                if(!isset($day['items']) || empty($day['items'])) {
                    $errors[] = "Day " . ($i+1) . ": Please select at least one custom item.";
                    break;
                }
                $selected_items = $day['items'];
                $ids = implode(',', array_map('intval', $selected_items));
                $items_data = $dao->getData("price_per_head", "items", "id IN ($ids)");
                foreach($items_data as $itm) {
                    $price_per_person += floatval($itm['price_per_head']);
                }
                $custom_items_json = "'" . mysqli_real_escape_string($con, json_encode($selected_items)) . "'";
            } else {
                if(empty($day['package_id'])) {
                    $errors[] = "Day " . ($i+1) . ": Please select a menu package.";
                    break;
                }
                $package_id = intval($day['package_id']);
                $pkg = $dao->getData("price", "menu_packages", "id=$package_id");
                if(!empty($pkg)) $price_per_person = $pkg[0]['price'];
            }

            $total_amount = $price_per_person * $guest_count;
            $total_grand += $total_amount;
            
            $advance_amount = $total_amount * 0.50;

            $evt = mysqli_real_escape_string($con, $event_type);
            $loc = mysqli_real_escape_string($con, $location);
            $notes = mysqli_real_escape_string($con, $special_notes);
            $evt_time = mysqli_real_escape_string($con, $event_time);

            $sql = "INSERT INTO bookings (customer_id, event_type, guest_count, event_date, event_time, event_days, location, package_id, is_custom, custom_items, total_amount, advance_amount, payment_status, booking_status, special_notes) 
                    VALUES ($user_id, '$evt', $guest_count, '$event_date', '$evt_time', 1, '$loc', $package_id, $is_custom, $custom_items_json, $total_amount, $advance_amount, 'pending', 'pending', '$notes')";
            
            if(mysqli_query($con, $sql)) {
                $booking_ids[] = mysqli_insert_id($con);
            } else {
                $errors[] = "Database Error on Day " . ($i+1) . ": " . mysqli_error($con);
                break;
            }
        }

        if(empty($errors) && !empty($booking_ids)) {
            // Redirect to payment page with booking IDs
            $ids_str = implode(',', $booking_ids);
            echo "<script>window.location='payment.php?ids=$ids_str';</script>";
            exit();
        } else {
            // Rollback if there was an error in multi-insert
            if(!empty($booking_ids)) {
                $ids_str = implode(',', $booking_ids);
                mysqli_query($con, "DELETE FROM bookings WHERE id IN ($ids_str)");
            }
        }
    }
}
?>

<style>
/* Card Selection Styles */
.selection-card {
    background: var(--card-bg);
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    height: 100%;
}
.selection-card:hover {
    border-color: rgba(212, 175, 55, 0.5);
    transform: translateY(-3px);
}
.selection-card input[type="radio"], .selection-card input[type="checkbox"] {
    display: none;
}
.selection-card input:checked + .card-content {
    background: rgba(212, 175, 55, 0.15);
}
.selection-card input:checked ~ .card-border {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 2px solid var(--primary-gold);
    border-radius: 10px;
    pointer-events: none;
}
.selection-card input:checked ~ .check-icon {
    display: block !important;
}
.card-content {
    padding: 15px;
    height: 100%;
}
.check-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    color: var(--primary-gold);
    font-size: 1.5rem;
    display: none;
    background: #000;
    border-radius: 50%;
}
.pkg-img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 10px;
}
.item-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}
.day-block {
    background: rgba(0,0,0,0.4);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
    position: relative;
}
.day-number-badge {
    position: absolute;
    top: -15px;
    left: 20px;
    background: var(--primary-gold);
    color: #000;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}
.remove-day-btn {
    position: absolute;
    top: 15px;
    right: 20px;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="mb-4 text-center">
            <h2 class="brand-font text-gold" style="color:var(--primary-gold)">Plan Your Events</h2>
            <p class="text-muted">Create a multi-day itinerary with customized menus and guest counts for each specific date.</p>
        </div>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger" style="background: rgba(220,53,69,0.1); border-color: #dc3545; color: #ff6b6b;">
                <ul class="mb-0">
                    <?php foreach($errors as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <!-- Global Settings -->
            <div class="card mb-4 border-0" style="background: rgba(0,0,0,0.3);">
                <div class="card-body">
                    <h5 class="brand-font text-gold mb-3" style="color:var(--primary-gold)">Global Event Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Event Type *</label>
                            <select name="event_type" class="form-control" style="background: var(--card-bg); color:white;" required>
                                <option value="">Select Event Type</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Engagement">Engagement</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Corporate">Corporate</option>
                                <option value="Housewarming">Housewarming</option>
                                <option value="Church Event">Church Event</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Location / Venue *</label>
                            <input type="text" name="location" class="form-control" placeholder="Enter full address" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted">Special Notes / Requirements</label>
                            <textarea name="special_notes" class="form-control" rows="2" placeholder="Any dietary restrictions, special requests, etc."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Days Container -->
            <div id="daysContainer">
                <!-- JS will inject days here -->
            </div>

            <div class="text-center mb-5">
                <button type="button" class="btn btn-outline-gold" onclick="addDayBlock()"><i class="fa fa-plus me-1"></i> Add Another Event Date</button>
            </div>

            <!-- Footer Estimate & Submit -->
            <div class="card p-4 mb-4" style="background: rgba(0,0,0,0.5); border: 1px dashed var(--primary-gold);">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="brand-font text-gold mb-1" style="color: var(--primary-gold);">Grand Total</h4>
                        <p class="text-muted small mb-0">Advance amount will be calculated on the next step.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h3 class="fw-bold text-success mb-0" id="grand_total">₹0.00</h3>
                    </div>
                </div>
            </div>

            <div class="text-end mb-5">
                <button type="submit" name="btnProceed" class="btn btn-gold btn-lg">Proceed to Payment <i class="fa fa-arrow-right ms-2"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- HTML Templates for JS -->
<template id="dayBlockTemplate">
    <div class="day-block" id="day_block_{INDEX}">
        <div class="day-number-badge">Event Day {DAY_NUM}</div>
        <button type="button" class="btn btn-sm btn-outline-danger remove-day-btn" onclick="removeDayBlock({INDEX})"><i class="fa fa-times"></i></button>
        
        <div class="row mt-3">
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Date *</label>
                <input type="text" name="days[{INDEX}][date]" class="form-control day-date" placeholder="Select Date" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Time *</label>
                <input type="time" name="days[{INDEX}][time]" class="form-control day-time" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Guest Count *</label>
                <input type="number" name="days[{INDEX}][guests]" class="form-control day-guests" min="10" value="100" required onkeyup="checkCapacity(this); calculateGrandTotal();" onchange="checkCapacity(this); calculateGrandTotal();">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label text-muted">Menu Mode *</label>
                <select name="days[{INDEX}][menu_mode]" class="form-control day-mode" style="background: var(--card-bg); color:white;" onchange="toggleMode({INDEX})">
                    <option value="package">Standard Package</option>
                    <option value="custom">Custom Menu</option>
                </select>
            </div>
        </div>

        <!-- Packages Grid -->
        <div class="packages-section" id="packages_sec_{INDEX}">
            <h6 class="text-gold mb-3" style="color:var(--primary-gold)">Select a Package</h6>
            <div class="row g-3">
                <?php foreach($packages as $p): ?>
                <div class="col-md-4">
                    <label class="selection-card w-100">
                        <input type="radio" name="days[{INDEX}][package_id]" value="<?= $p['id'] ?>" class="pkg-radio" data-price="<?= $p['price'] ?>" onchange="calculateGrandTotal()">
                        <div class="card-content text-center">
                            <img src="../assets/uploads/<?= $p['image'] ?>" class="pkg-img" onerror="this.src='../assets/images/default.png'">
                            <h6 class="mb-1 text-white"><?= htmlspecialchars($p['package_name']) ?></h6>
                            <?php 
                                $p_items = json_decode($p['menu_items'], true);
                                $p_count = is_array($p_items) ? count($p_items) : 0;
                                $p_items_json = json_encode($p_items);
                            ?>
                            <small class="text-muted d-block mb-2">
                                <?= $p_count ?> Items Included 
                                <button type="button" class="btn btn-link p-0 text-gold ms-1 border-0 align-baseline" onclick="showPackageDetails(event, '<?= htmlspecialchars($p['package_name'], ENT_QUOTES) ?>', <?= htmlspecialchars($p_items_json, ENT_QUOTES) ?>)" title="View included items" style="color: var(--primary-gold); outline: none; box-shadow: none;">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </small>
                            <span class="badge bg-gold text-dark" style="background:var(--primary-gold)">₹<?= number_format($p['price'],0) ?> / head</span>
                        </div>
                        <div class="card-border"></div>
                        <i class="fa fa-check-circle check-icon"></i>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Custom Items Grid -->
        <div class="custom-section" id="custom_sec_{INDEX}" style="display:none;">
            <h6 class="text-gold mb-3" style="color:var(--primary-gold)">Select Custom Menu Items</h6>
            <div class="row g-2">
                <?php foreach($custom_items as $ci): ?>
                <div class="col-md-4">
                    <label class="selection-card w-100 h-100">
                        <input type="checkbox" name="days[{INDEX}][items][]" value="<?= $ci['id'] ?>" class="item-checkbox" data-price="<?= $ci['price_per_head'] ?>" onchange="calculateGrandTotal()">
                        <div class="card-content d-flex align-items-center p-2">
                            <?php if(!empty($ci['image'])): ?>
                                <img src="../assets/uploads/<?= $ci['image'] ?>" class="item-img" onerror="this.style.display='none'">
                            <?php else: ?>
                                <div style="width:50px;height:50px;border-radius:8px;background:rgba(212,175,55,0.2);margin-right:15px;display:flex;align-items:center;justify-content:center;"><i class="fa fa-utensils text-gold"></i></div>
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-0 text-white" style="font-size:0.9rem;"><?= htmlspecialchars($ci['name']) ?></h6>
                                <span class="text-gold" style="color:var(--primary-gold); font-size:0.8rem;">₹<?= number_format($ci['price_per_head'],0) ?></span>
                            </div>
                        </div>
                        <div class="card-border"></div>
                        <i class="fa fa-check-circle check-icon" style="top:5px;right:5px;font-size:1.2rem;"></i>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="text-end mt-3 border-top pt-2" style="border-color: rgba(255,255,255,0.1) !important;">
            <span class="text-muted me-2">Day Total Estimate:</span>
            <span class="fs-5 fw-bold text-gold day-total" style="color:var(--primary-gold)">₹0.00</span>
        </div>
    </div>
</template>

<script>
const blockedDates = <?= json_encode($blocked_dates_reasons) ?>;
const dateCapacities = <?= json_encode($date_capacities) ?>;
const defaultCapacity = <?= intval($default_capacity) ?>;
const totalStaff = <?= json_encode($capacity_data['total_staff']) ?>;
const leavesByDate = <?= json_encode($capacity_data['leaves_by_date']) ?>;
const assignedByDate = <?= json_encode($capacity_data['assigned_by_date']) ?>;
let dayIndex = 0;
let dayCount = 0;

function checkCapacity(element) {
    const block = element.closest('.day-block');
    if (!block) return;
    
    const dateInput = block.querySelector('.day-date');
    const guestInput = block.querySelector('.day-guests');
    
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
            calculateGrandTotal();
        }
    }
}

function addDayBlock() {
    const template = document.getElementById('dayBlockTemplate').innerHTML;
    const html = template.replace(/\{INDEX\}/g, dayIndex).replace(/\{DAY_NUM\}/g, dayCount + 1);
    
    document.getElementById('daysContainer').insertAdjacentHTML('beforeend', html);
    
    // Initialize Flatpickr on the newly added date field
    const dateInput = document.querySelector(`#day_block_${dayIndex} .day-date`);
    flatpickr(dateInput, {
        minDate: new Date().fp_incr(3), // 3 days from now
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
                checkCapacity(instance.element);
            }
            calculateGrandTotal();
        }
    });
    
    // Hide remove button if it's the first block
    if(dayCount === 0) {
        document.querySelector('#day_block_' + dayIndex + ' .remove-day-btn').style.display = 'none';
    }
    
    dayIndex++;
    dayCount++;
    reindexDays();
    calculateGrandTotal();
}

function removeDayBlock(index) {
    if(dayCount <= 1) return; // Prevent removing the last day
    document.getElementById('day_block_' + index).remove();
    dayCount--;
    reindexDays();
    calculateGrandTotal();
}

function reindexDays() {
    const blocks = document.querySelectorAll('.day-block');
    blocks.forEach((block, idx) => {
        block.querySelector('.day-number-badge').innerText = 'Event Day ' + (idx + 1);
    });
}

function toggleMode(index) {
    const mode = document.querySelector(`select[name="days[${index}][menu_mode]"]`).value;
    const pkgSec = document.getElementById(`packages_sec_${index}`);
    const cstSec = document.getElementById(`custom_sec_${index}`);
    
    if(mode === 'custom') {
        pkgSec.style.display = 'none';
        cstSec.style.display = 'block';
        // uncheck radios to avoid submitting package data when custom is selected
        document.querySelectorAll(`#packages_sec_${index} .pkg-radio`).forEach(r => r.checked = false);
    } else {
        pkgSec.style.display = 'block';
        cstSec.style.display = 'none';
        // uncheck checkboxes to avoid submitting custom items when package is selected
        document.querySelectorAll(`#custom_sec_${index} .item-checkbox`).forEach(c => c.checked = false);
    }
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    const blocks = document.querySelectorAll('.day-block');
    
    blocks.forEach(block => {
        const indexStr = block.id.replace('day_block_', '');
        const guests = parseInt(block.querySelector('.day-guests').value) || 0;
        const mode = block.querySelector('.day-mode').value;
        let pricePerHead = 0;
        
        if(mode === 'package') {
            const checkedRadio = block.querySelector('.pkg-radio:checked');
            if(checkedRadio) {
                pricePerHead = parseFloat(checkedRadio.getAttribute('data-price'));
            }
        } else {
            const checkedBoxes = block.querySelectorAll('.item-checkbox:checked');
            checkedBoxes.forEach(cb => {
                pricePerHead += parseFloat(cb.getAttribute('data-price'));
            });
        }
        
        const dayTotal = pricePerHead * guests;
        block.querySelector('.day-total').innerText = '₹' + dayTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        grandTotal += dayTotal;
    });
    
    document.getElementById('grand_total').innerText = '₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function showPackageDetails(event, name, items) {
    event.stopPropagation();
    event.preventDefault();
    
    let itemsHtml = '<ul class="text-start list-group list-group-flush" style="background:transparent; margin:0; padding:0;">';
    items.forEach(function(item) {
        itemsHtml += `<li class="list-group-item text-white border-0 bg-transparent py-2"><i class="fa fa-check-circle text-gold me-2" style="color:var(--primary-gold);"></i> ${item}</li>`;
    });
    itemsHtml += '</ul>';

    Swal.fire({
        title: name,
        html: itemsHtml,
        icon: 'info',
        confirmButtonText: 'Close',
        customClass: {
            popup: 'swal2-popup',
            title: 'swal2-title',
            confirmButton: 'swal2-confirm'
        }
    });
}


// Initialize with one day block
document.addEventListener('DOMContentLoaded', () => {
    addDayBlock();
    
    // Pre-select package if package_id is passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedPkgId = urlParams.get('package_id');
    if (preselectedPkgId) {
        const radio = document.querySelector(`.pkg-radio[value="${preselectedPkgId}"]`);
        if (radio) {
            radio.checked = true;
            calculateGrandTotal();
        }
    }
});
</script>

<?php require_once('footer.php'); ?>
