<?php require_once('header.php'); 
$dao = new DataAccess();



// Get Customers
$customers = $dao->getData("*", "users", "role='customer' ORDER BY id DESC");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Customers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-dark mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($customers)): foreach($customers as $c): 
                            $booking_count = $dao->count("id", "bookings", "customer_id=".$c['id']);
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><i class="fa fa-phone me-1 text-muted"></i> <?= htmlspecialchars($c['phone']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= $booking_count ?> Bookings</span></td>
                            <td>
                                <?php if($c['active_status']): ?>
                                    <span class="text-success"><i class="fa fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fa fa-times-circle"></i> Banned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($c['active_status']): ?>
                                    <a href="../status/update_status.php?id=<?= $c['id'] ?>&status=0&type=customer" class="btn btn-sm btn-outline-danger" onclick="return confirm('Ban this customer? They will not be able to login.');"><i class="fa fa-ban"></i> Ban User</a>
                                <?php else: ?>
                                    <a href="../status/update_status.php?id=<?= $c['id'] ?>&status=1&type=customer" class="btn btn-sm btn-outline-success" onclick="return confirm('Reactivate this customer?');"><i class="fa fa-check"></i> Reactivate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No customers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
