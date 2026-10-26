<?php require_once('header.php'); 
$dao = new DataAccess();



// Get Bookings
$bookings = $dao->getDataJoin(
    array("bookings.*", "users.name as customer_name", "users.phone", "menu_packages.package_name"),
    "bookings",
    "1 ORDER BY bookings.id DESC",
    array(
        "users" => array("bookings.customer_id=users.id", "innerjoin"),
        "menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin")
    )
);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Event Bookings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-dark mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Info</th>
                            <th>Event Details</th>
                            <th>Financials</th>
                            <th>Status</th>
                            <th>Staff Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($bookings)): foreach($bookings as $bk): ?>
                        <tr>
                            <td>#<?= $bk['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($bk['customer_name']) ?></strong><br>
                                <small class="text-muted"><i class="fa fa-phone me-1"></i> <?= htmlspecialchars($bk['phone']) ?></small>
                            </td>
                            <td>
                                <strong class="text-gold" style="color:var(--primary-gold);"><?= htmlspecialchars($bk['event_type']) ?></strong><br>
                                <small><i class="fa fa-calendar me-1"></i> <?= date('d M Y', strtotime($bk['event_date'])) ?></small>
                                <?php if(!empty($bk['event_time'])): ?>
                                    <small class="ms-2"><i class="fa fa-clock me-1"></i> <?= date('h:i A', strtotime($bk['event_time'])) ?></small>
                                <?php endif; ?>
                                <br>
                                <small><i class="fa fa-users me-1"></i> <?= $bk['guest_count'] ?> Guests (<?= $bk['event_days'] ?> Days)</small><br>
                                <?php if($bk['is_custom']): ?>
                                    <small class="text-info"><i class="fa fa-list-check"></i> Custom Menu (<?php $c_items = json_decode($bk['custom_items']); echo is_array($c_items) ? count($c_items) : 0; ?> items)</small>
                                <?php else: ?>
                                    <small class="text-info"><?= htmlspecialchars($bk['package_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span>Total: ₹<?= number_format($bk['total_amount'], 2) ?></span><br>
                                <small class="text-muted">Adv: ₹<?= number_format($bk['advance_amount'], 2) ?></small><br>
                                <?php if($bk['payment_status'] == 'pending'): ?>
                                    <span class="badge border border-warning text-warning mt-1" style="font-size: 0.75rem;">Pending</span>
                                <?php elseif($bk['payment_status'] == 'advance_paid'): ?>
                                    <span class="badge border border-success text-success mt-1" style="font-size: 0.75rem;">Advance Paid</span>
                                <?php elseif($bk['payment_status'] == 'fully_paid'): ?>
                                    <span class="badge bg-success mt-1" style="font-size: 0.75rem;">Fully Paid</span>
                                <?php elseif($bk['payment_status'] == 'refunded'): ?>
                                    <span class="badge border border-info text-info mt-1" style="font-size: 0.75rem;">Refunded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($bk['booking_status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif($bk['booking_status'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif($bk['booking_status'] == 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php elseif($bk['booking_status'] == 'cancelled'): ?>
                                    <span class="badge bg-secondary">Cancelled</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark"><?= ucfirst($bk['booking_status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if($bk['booking_status'] == 'approved' || $bk['booking_status'] == 'completed') {
                                    $staff_count = $dao->count("id", "booking_staff", "booking_id=".$bk['id']);
                                    echo "<span class='badge bg-primary'>$staff_count Staff Assigned</span>";
                                    echo "<br><a href='staff_assign.php?id=".$bk['id']."' class='btn btn-link btn-sm text-gold px-0' style='color:var(--primary-gold);'>Manage Staff</a>";
                                } else {
                                    echo "<span class='text-muted small'>N/A</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($bk['booking_status'] == 'pending'): ?>
                                    <a href="../approve/approve_booking.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-success mb-1 w-100" onclick="return confirm('Approve booking and auto-allocate staff?');"><i class="fa fa-check"></i> Approve</a>
                                    <a href="../issue/reject_booking.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-danger w-100" onclick="return confirm('Reject this booking?');"><i class="fa fa-times"></i> Reject</a>
                                <?php elseif($bk['booking_status'] == 'approved'): ?>
                                    <a href="../status/complete_booking.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-info text-dark mb-1 w-100" onclick="return confirm('Are you sure you want to mark this event as completed?');"><i class="fa fa-check-double"></i> Mark Completed</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary w-100 disabled mb-1">Action Done</button>
                                <?php endif; ?>
                                
                                <?php if($bk['payment_status'] != 'fully_paid' && $bk['booking_status'] != 'cancelled' && $bk['booking_status'] != 'rejected'): ?>
                                    <a href="../status/update_payment.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-warning text-dark w-100" onclick="return confirm('Mark this booking as fully paid?');"><i class="fa fa-wallet"></i> Mark Fully Paid</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
