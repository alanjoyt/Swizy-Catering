<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];
$role = ucfirst($_SESSION['role']);




// Get Assignments
$assignments = $dao->getDataJoin(
    array("booking_staff.id as bs_id", "booking_staff.assignment_status", "bookings.event_type", "bookings.event_date", "bookings.event_time", "bookings.location", "menu_packages.package_name"),
    "booking_staff",
    "booking_staff.user_id=$user_id ORDER BY bookings.event_date ASC",
    array(
        "bookings" => array("booking_staff.booking_id=bookings.id", "innerjoin"),
        "menu_packages" => array("bookings.package_id=menu_packages.id", "innerjoin")
    )
);
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="brand-font">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h4>
        <p class="text-muted">Role: <span class="text-gold" style="color:var(--primary-gold);"><?= str_replace('_', ' ', $role) ?></span></p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">My Upcoming Assignments</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-dark mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event Type</th>
                        <th>Location</th>
                        <th>Menu / Package</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($assignments)): foreach($assignments as $as): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary-gold);"><?= date('d M Y', strtotime($as['event_date'])) ?></strong>
                            <?php if(!empty($as['event_time'])): ?>
                                <br><small class="text-muted"><i class="fa fa-clock me-1"></i> <?= date('h:i A', strtotime($as['event_time'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($as['event_type']) ?></td>
                        <td><i class="fa fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($as['location']) ?></td>
                        <td><?= htmlspecialchars($as['package_name']) ?></td>
                        <td>
                            <?php if($as['assignment_status'] == 'assigned'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif($as['assignment_status'] == 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($as['assignment_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($as['assignment_status'] == 'assigned'): ?>
                                <a href="../status/complete_assignment.php?id=<?= $as['bs_id'] ?>&user_id=<?= $user_id ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this task as completed?');"><i class="fa fa-check"></i> Mark Complete</a>
                            <?php else: ?>
                                <span class="text-muted small"><i class="fa fa-check-circle text-success"></i> Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">You have no assignments at the moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
