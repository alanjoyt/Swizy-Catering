<?php require_once('header.php'); 
$dao = new DataAccess();

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$booking_id) {
    echo "<script>window.location='bookings.php';</script>";
    exit();
}

// Get Booking Details
$bk = $dao->getDataJoin(
    array("bookings.*", "users.name as customer_name"),
    "bookings",
    "bookings.id=$booking_id",
    array("users" => array("bookings.customer_id=users.id", "innerjoin"))
);

if(empty($bk)) {
    swalRedirect("Booking not found", "bookings.php", "error", "Error");
    exit();
}
$booking = $bk[0];




// Add Staff
if(isset($_POST['btnAdd'])) {
    $user_id = intval($_POST['staff_id']);
    
    // Check if already assigned
    if($dao->count("id", "booking_staff", "booking_id=$booking_id AND user_id=$user_id") > 0) {
        swalRedirect("Staff is already assigned to this event!", "staff_assign.php?id=$booking_id", "error", "Error");
    } else {
        $staff_info = $dao->getData("role", "users", "id=$user_id");
        $role = $staff_info[0]['role'];
        
        $dao->insert(array(
            "booking_id" => $booking_id,
            "user_id" => $user_id,
            "assigned_role" => $role,
            "assignment_status" => "assigned"
        ), "booking_staff");
        swalRedirect("Staff added successfully!", "staff_assign.php?id=$booking_id", "success", "Success");
    }
}

// Get Assigned Staff
$assigned = $dao->getDataJoin(
    array("booking_staff.id as bs_id", "booking_staff.assignment_status", "users.name", "users.role", "users.phone"),
    "booking_staff",
    "booking_staff.booking_id=$booking_id ORDER BY users.role",
    array("users" => array("booking_staff.user_id=users.id", "innerjoin"))
);

// Get available staff for dropdown (active and available)
$available_staff = $dao->getData("*", "users", "role IN ('chef', 'server', 'site_manager') AND active_status=1 AND availability_status=1 ORDER BY role, name");
?>

<div class="container-fluid">
    <div class="mb-3">
        <a href="bookings.php" class="btn btn-sm btn-outline-light"><i class="fa fa-arrow-left"></i> Back to Bookings</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Event Details #<?= $booking['id'] ?></h6>
                </div>
                <div class="card-body">
                    <p><strong>Customer:</strong> <?= htmlspecialchars($booking['customer_name']) ?></p>
                    <p><strong>Event:</strong> <span class="text-gold" style="color:var(--primary-gold);"><?= htmlspecialchars($booking['event_type']) ?></span></p>
                    <p><strong>Date:</strong> <?= date('d M Y', strtotime($booking['event_date'])) ?></p>
                    <p><strong>Guests:</strong> <?= $booking['guest_count'] ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($booking['booking_status']) ?></p>
                    
                    <hr class="border-secondary">
                    
                    <h6 class="text-muted mb-3">Add Staff Manually</h6>
                    <form method="POST">
                        <div class="mb-3">
                            <select name="staff_id" class="form-control" style="background:var(--dark-bg); color:white;" required>
                                <option value="">Select Staff Member...</option>
                                <?php foreach($available_staff as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= ucfirst($s['role']) ?> - <?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="btnAdd" class="btn btn-sm btn-gold w-100"><i class="fa fa-plus"></i> Add to Event</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Assigned Staff</h6>
                    <span class="badge bg-primary"><?= count($assigned) ?> Total</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Task Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($assigned)): foreach($assigned as $as): ?>
                                <tr>
                                    <td>
                                        <?php if($as['role'] == 'chef'): ?>
                                            <span class="badge bg-danger"><i class="fa fa-fire"></i> Chef</span>
                                        <?php elseif($as['role'] == 'server'): ?>
                                            <span class="badge bg-info"><i class="fa fa-concierge-bell"></i> Server</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fa fa-user-tie"></i> Site Manager</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($as['name']) ?></td>
                                    <td><i class="fa fa-phone text-muted me-1"></i> <?= htmlspecialchars($as['phone']) ?></td>
                                    <td>
                                        <?php if($as['assignment_status'] == 'assigned'): ?>
                                            <span class="text-warning"><i class="fa fa-clock"></i> Pending</span>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fa fa-check-circle"></i> Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../delete/delete_assignment.php?id=<?= $as['bs_id'] ?>&booking_id=<?= $booking_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this staff member from the event?');"><i class="fa fa-times"></i> Remove</a>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No staff assigned yet. Wait for auto-allocation or add manually.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
