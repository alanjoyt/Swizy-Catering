<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];



// Get Bookings
$bookings = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name", "menu_packages.image", "menu_packages.menu_items"),
    "bookings",
    "customer_id=$user_id ORDER BY bookings.id DESC",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">My Bookings</h5>
        <a href="book.php" class="btn btn-sm btn-gold"><i class="fa fa-plus me-1"></i> New Booking</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-dark mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Event Type</th>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Guests & Days</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($bookings)): foreach($bookings as $bk): ?>
                    <tr>
                        <td>#<?= $bk['id'] ?></td>
                        <td><?= htmlspecialchars($bk['event_type']) ?></td>
                        <td>
                            <?php if($bk['is_custom']): ?>
                                <div class="d-flex align-items-center">
                                    <div style="width:40px; height:40px; border-radius:50%; background:var(--primary-gold); color:black; display:flex; align-items:center; justify-content:center; margin-right:8px;"><i class="fa fa-list"></i></div>
                                    <div>
                                        <strong>Custom Menu</strong><br>
                                        <small class="text-muted"><?php $c_items = json_decode($bk['custom_items']); echo is_array($c_items) ? count($c_items) : 0; ?> items</small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center">
                                    <img src="../assets/uploads/<?= htmlspecialchars($bk['image']) ?>" alt="package" width="40" height="40" class="rounded-circle me-2" style="object-fit:cover; border:1px solid var(--primary-gold);">
                                    <div>
                                        <strong><?= htmlspecialchars($bk['package_name']) ?></strong><br>
                                        <small class="text-muted"><?php $pkg_items = json_decode($bk['menu_items'], true); echo is_array($pkg_items) ? count($pkg_items) : 0; ?> items</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($bk['event_date'])) ?>
                            <?php if(!empty($bk['event_time'])): ?>
                                <br><small class="text-muted"><i class="fa fa-clock me-1"></i> <?= date('h:i A', strtotime($bk['event_time'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $bk['guest_count'] ?> Guests<br><small class="text-muted"><?= $bk['event_days'] ?> Day(s)</small></td>
                        <td>₹<?= number_format($bk['total_amount'], 2) ?><br><small class="text-muted">Adv: ₹<?= number_format($bk['advance_amount'], 2) ?></small></td>
                        <td>
                            <?php if($bk['booking_status'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                            <?php elseif($bk['booking_status'] == 'approved'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php elseif($bk['booking_status'] == 'cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($bk['booking_status']) ?></span>
                            <?php endif; ?>
                            
                            <br>
                            <?php if($bk['payment_status'] == 'pending'): ?>
                                <span class="badge border border-warning text-warning mt-1">Payment Pending</span>
                            <?php elseif($bk['payment_status'] == 'advance_paid'): ?>
                                <span class="badge border border-success text-success mt-1">Advance Paid</span>
                            <?php elseif($bk['payment_status'] == 'fully_paid'): ?>
                                <span class="badge border border-success text-success mt-1">Fully Paid</span>
                            <?php elseif($bk['payment_status'] == 'refunded'): ?>
                                <span class="badge border border-info text-info mt-1">Refunded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-gold mb-1" style="color: var(--primary-gold); border-color: var(--primary-gold);" data-bs-toggle="modal" data-bs-target="#bookingDetailsModal<?= $bk['id'] ?>"><i class="fa fa-info-circle"></i> Details</button>
                            
                            <?php if($bk['booking_status'] == 'pending' || $bk['booking_status'] == 'approved'): ?>
                                <?php 
                                    $ev_date = strtotime(date('Y-m-d', strtotime($bk['event_date'])));
                                    $curr_date = strtotime(date('Y-m-d'));
                                    $days_left = ($ev_date - $curr_date) / (60 * 60 * 24);
                                ?>
                                <?php if($bk['booking_status'] == 'approved' && $days_left >= 2): ?>
                                    <a href="update_booking.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-outline-info mb-1"><i class="fa fa-edit"></i> Edit</a>
                                <?php endif; ?>
                                
                                <?php if($days_left >= 3): ?>
                                    <a href="javascript:void(0);" onclick="confirmCancellation(<?= $bk['id'] ?>, '<?= $bk['payment_status'] ?>')" class="btn btn-sm btn-outline-danger mb-1"><i class="fa fa-times-circle"></i> Cancel</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary mb-1" onclick="alert('Cancellations are only allowed 3 or more days prior to the event.');" title="Too late to cancel"><i class="fa fa-times-circle"></i> Cancel</button>
                                <?php endif; ?>
                                
                                <?php if($bk['booking_status'] == 'approved' && $bk['payment_status'] == 'pending'): ?>
                                    <a href="payment.php?ids=<?= $bk['id'] ?>" class="btn btn-sm btn-success mt-1"><i class="fa fa-credit-card"></i> Pay Advance</a>
                                <?php endif; ?>
                            <?php elseif($bk['booking_status'] == 'completed'): ?>
                                <a href="feedback.php?id=<?= $bk['id'] ?>" class="btn btn-sm btn-outline-warning text-gold" style="color:var(--primary-gold); border-color:var(--primary-gold);"><i class="fa fa-star"></i> Leave Review</a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No bookings found. <a href="book.php" style="color:var(--primary-gold);">Book your first event!</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(!empty($bookings)): foreach($bookings as $bk): ?>
    <!-- Modal for booking details -->
    <div class="modal fade" id="bookingDetailsModal<?= $bk['id'] ?>" tabindex="-1" aria-labelledby="bookingDetailsModalLabel<?= $bk['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content text-white" style="background: #1a1a1a; border: 1px solid var(--primary-gold); border-radius: 12px;">
                <div class="modal-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid rgba(212, 175, 55, 0.2) !important; padding: 15px 20px;">
                    <h5 class="modal-title brand-font text-gold fs-4" id="bookingDetailsModalLabel<?= $bk['id'] ?>" style="color:var(--primary-gold);">
                        <i class="fa fa-info-circle me-2"></i>Booking Details #<?= $bk['id'] ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-4">
                    <div class="row g-4">
                        <!-- Left Column: Details -->
                        <div class="col-md-6 border-end" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <h6 class="text-gold border-bottom pb-2 mb-3" style="color: var(--primary-gold); border-color: rgba(212,175,55,0.2) !important;">
                                <i class="fa fa-calendar-alt me-2"></i>Event Info
                            </h6>
                            <table class="table table-borderless table-dark bg-transparent m-0 p-0 text-white align-middle" style="--bs-table-bg: transparent;">
                                <tr>
                                    <td class="text-muted py-2 ps-0" style="width: 35%;">Event Type:</td>
                                    <td class="fw-semibold py-2"><?= htmlspecialchars($bk['event_type']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2 ps-0">Venue / Location:</td>
                                    <td class="py-2"><?= htmlspecialchars($bk['location']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2 ps-0">Guest Count:</td>
                                    <td class="py-2"><?= $bk['guest_count'] ?> Guests</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2 ps-0">Duration:</td>
                                    <td class="py-2"><?= $bk['event_days'] ?> Day(s)</td>
                                </tr>
                            </table>

                            <!-- Dates List -->
                            <div class="mt-3 p-3 rounded" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255,255,255,0.05);">
                                <strong class="text-gold small d-block mb-2" style="color: var(--primary-gold);">Scheduled Date(s) & Time:</strong>
                                <ul class="list-unstyled mb-0">
                                    <?php 
                                    for ($i = 0; $i < $bk['event_days']; $i++): 
                                        $d = date('d M Y', strtotime($bk['event_date'] . " + $i days"));
                                    ?>
                                        <li class="d-flex justify-content-between align-items-center mb-1 text-white-50 small">
                                            <span><i class="fa fa-calendar-day text-gold me-2" style="color: var(--primary-gold); font-size: 0.8rem;"></i>Day <?= $i+1 ?>: <?= $d ?></span>
                                            <?php if(!empty($bk['event_time'])): ?>
                                                <span class="badge bg-dark border border-gold text-gold" style="color:var(--primary-gold); border-color:var(--primary-gold) !important; font-weight: normal;"><i class="fa fa-clock me-1"></i><?= date('h:i A', strtotime($bk['event_time'])) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>

                            <!-- Special Notes -->
                            <div class="mt-3">
                                <h6 class="text-gold small mb-2" style="color: var(--primary-gold);"><i class="fa fa-comment-dots me-2"></i>Special Notes:</h6>
                                <div class="p-3 rounded text-muted small" style="background: rgba(0,0,0,0.25); border-left: 3px solid var(--primary-gold); font-style: italic;">
                                    <?= !empty($bk['special_notes']) ? htmlspecialchars($bk['special_notes']) : 'No special notes or requirements specified.' ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Manager & Menu -->
                        <div class="col-md-6">
                            <!-- Site Manager Info -->
                            <h6 class="text-gold border-bottom pb-2 mb-3" style="color: var(--primary-gold); border-color: rgba(212,175,55,0.2) !important;">
                                <i class="fa fa-user-tie me-2"></i>Assigned Coordinator
                            </h6>
                            <?php 
                                $manager_query = $dao->query("
                                    SELECT u.name, u.phone, u.email 
                                    FROM booking_staff bs 
                                    JOIN users u ON bs.user_id = u.id 
                                    WHERE bs.booking_id = " . intval($bk['id']) . " 
                                      AND bs.assigned_role = 'site_manager' 
                                      AND bs.assignment_status != 'cancelled'
                                ");
                                $manager = (!empty($manager_query)) ? $manager_query[0] : null;
                            ?>
                            <?php if ($manager): ?>
                                <div class="d-flex align-items-center mb-4 p-3 rounded" style="background: rgba(212,175,55,0.05); border: 1px solid rgba(212,175,55,0.15);">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gold); color: black; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: bold; margin-right: 15px;">
                                        <?= strtoupper(substr($manager['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong class="text-white d-block"><?= htmlspecialchars($manager['name']) ?></strong>
                                        <small class="text-muted d-block" style="font-size:0.8rem;"><i class="fa fa-phone me-1 text-gold" style="color: var(--primary-gold);"></i><?= htmlspecialchars($manager['phone']) ?></small>
                                        <small class="text-muted d-block" style="font-size:0.8rem;"><i class="fa fa-envelope me-1 text-gold" style="color: var(--primary-gold);"></i><?= htmlspecialchars($manager['email']) ?></small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary py-3 text-center mb-4 text-white-50" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                                    <i class="fa fa-clock-rotate-left mb-2 d-block fs-5 text-muted"></i>
                                    <span style="font-size: 0.85rem;">Site manager allocation pending approval/payment.</span>
                                </div>
                            <?php endif; ?>

                            <!-- Menu Details -->
                            <h6 class="text-gold border-bottom pb-2 mb-2" style="color: var(--primary-gold); border-color: rgba(212,175,55,0.2) !important;">
                                <i class="fa fa-utensils me-2"></i>Menu Selection
                            </h6>
                            <div class="p-3 rounded" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255,255,255,0.05); max-height: 180px; overflow-y: auto;">
                                <?php if ($bk['is_custom']): ?>
                                    <strong class="text-gold d-block mb-2 small" style="color: var(--primary-gold);">Custom Menu:</strong>
                                    <?php 
                                        $custom_items_list = array();
                                        if (!empty($bk['custom_items'])) {
                                            $custom_ids = json_decode($bk['custom_items'], true);
                                            if (is_array($custom_ids) && !empty($custom_ids)) {
                                                $ids_str = implode(',', array_map('intval', $custom_ids));
                                                $custom_items_list = $dao->getData("name, category, price_per_head", "items", "id IN ($ids_str)");
                                            }
                                        }
                                    ?>
                                    <?php if (!empty($custom_items_list)): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($custom_items_list as $citm): ?>
                                                <span class="badge bg-dark border border-gold text-gold p-1" style="color: var(--primary-gold); border-color: var(--primary-gold) !important; font-weight: normal; font-size: 0.75rem;">
                                                    <i class="fa fa-check me-1" style="font-size:0.65rem;"></i><?= htmlspecialchars($citm['name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">No items selected.</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <strong class="text-gold d-block mb-1 small" style="color: var(--primary-gold);"><?= htmlspecialchars($bk['package_name']) ?></strong>
                                    <small class="text-muted d-block mb-2" style="font-size:0.75rem;">Package Included Items:</small>
                                    <?php 
                                    $pkg_items = json_decode($bk['menu_items'], true);
                                    if (is_array($pkg_items) && !empty($pkg_items)): 
                                    ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($pkg_items as $pitm): ?>
                                                <span class="badge bg-dark border border-gold text-gold p-1" style="color: var(--primary-gold); border-color: var(--primary-gold) !important; font-weight: normal; font-size: 0.75rem;">
                                                    <i class="fa fa-check me-1" style="font-size:0.65rem;"></i><?= htmlspecialchars($pitm) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">No package details found.</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Section -->
                    <div class="mt-4 p-3 rounded" style="background: rgba(212,175,55,0.03); border: 1px solid rgba(212,175,55,0.15);">
                        <h6 class="text-gold small mb-3 text-uppercase fw-semibold" style="color: var(--primary-gold); letter-spacing: 0.5px;"><i class="fa fa-file-invoice-dollar me-2"></i>Financial & Status Summary</h6>
                        <div class="row text-center g-3">
                            <div class="col-sm-4 border-end" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                <small class="text-muted d-block" style="font-size:0.75rem;">Total Cost</small>
                                <strong class="text-white fs-6">₹<?= number_format($bk['total_amount'], 2) ?></strong>
                            </div>
                            <div class="col-sm-4 border-end" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                                <?php if($bk['payment_status'] == 'fully_paid'): ?>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Amount Paid</small>
                                    <strong class="text-success fs-6">₹<?= number_format($bk['total_amount'], 2) ?></strong>
                                <?php else: ?>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">Advance Paid (50%)</small>
                                    <strong class="text-success fs-6">₹<?= number_format($bk['advance_amount'], 2) ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block" style="font-size:0.75rem;">Balance Due</small>
                                <?php 
                                    $balance = $bk['total_amount'] - $bk['advance_amount'];
                                    if($bk['payment_status'] == 'fully_paid') $balance = 0;
                                ?>
                                <strong class="text-warning fs-6">₹<?= number_format($balance, 2) ?></strong>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3" style="border-color: rgba(255, 255, 255, 0.08) !important;">
                            <div>
                                <span class="text-muted me-2 small">Booking Status:</span>
                                <?php if($bk['booking_status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                <?php elseif($bk['booking_status'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif($bk['booking_status'] == 'cancelled'): ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= ucfirst($bk['booking_status']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="text-muted me-2 small">Payment Status:</span>
                                <?php if($bk['payment_status'] == 'pending'): ?>
                                    <span class="badge border border-warning text-warning">Payment Pending</span>
                                <?php elseif($bk['payment_status'] == 'advance_paid'): ?>
                                    <span class="badge border border-success text-success">Advance Paid</span>
                                <?php elseif($bk['payment_status'] == 'fully_paid'): ?>
                                    <span class="badge border border-success text-success">Fully Paid</span>
                                <?php elseif($bk['payment_status'] == 'refunded'): ?>
                                    <span class="badge border border-info text-info">Refunded</span>
                                <?php else: ?>
                                    <span class="badge border border-secondary text-secondary"><?= ucfirst($bk['payment_status']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-between align-items-center" style="border-top: 1px solid rgba(212, 175, 55, 0.2) !important; padding: 15px 20px;">
                    <?php if($bk['payment_status'] == 'advance_paid' || $bk['payment_status'] == 'fully_paid'): ?>
                        <a href="invoice.php?ids=<?= $bk['id'] ?>" target="_blank" class="btn btn-sm btn-gold"><i class="fa fa-print me-1"></i> Print Invoice</a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; endif; ?>

<script>
function confirmCancellation(bookingId, paymentStatus) {
    let policyText = "";
    if (paymentStatus === 'advance_paid' || paymentStatus === 'fully_paid') {
        policyText = `
            <div class="text-start text-white p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(220, 53, 69, 0.3);">
                <h6 class="text-danger fw-bold"><i class="fa fa-info-circle me-2"></i>Cancellation & Refund Policy:</h6>
                <ul class="small mb-0 mt-2 ps-3 text-muted" style="list-style-type: disc;">
                    <li>Cancellations are allowed up to <strong>3 days prior</strong> to the event date.</li>
                    <li>Since payment has been made, a <strong>5% cancellation fee</strong> of the advance amount will be deducted.</li>
                    <li>The remaining <strong>95%</strong> will be refunded to your account automatically.</li>
                </ul>
            </div>
        `;
    } else {
        policyText = `
            <div class="text-start text-white p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255, 193, 7, 0.3);">
                <h6 class="text-warning fw-bold"><i class="fa fa-info-circle me-2"></i>Cancellation Policy:</h6>
                <ul class="small mb-0 mt-2 ps-3 text-muted" style="list-style-type: disc;">
                    <li>This booking is currently unpaid (payment status: pending).</li>
                    <li>You can cancel this booking for free with no cancellation fees.</li>
                </ul>
            </div>
        `;
    }

    Swal.fire({
        title: 'Cancel Booking?',
        html: `
            <div class="text-center">
                <p class="mb-3 text-white">Are you sure you want to cancel booking <strong>#${bookingId}</strong>?</p>
                ${policyText}
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Cancel Booking',
        cancelButtonText: 'No, Keep Booking',
        customClass: {
            popup: 'swal2-popup',
            title: 'swal2-title text-danger',
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../issue/cancel_booking.php?id=${bookingId}`;
        }
    });
}
</script>

<?php require_once('footer.php'); ?>
