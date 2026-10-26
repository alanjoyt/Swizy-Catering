<?php require_once('header.php'); 
$dao = new DataAccess();



// Get Staff
$staff = $dao->getData("*", "users", "role IN ('chef', 'server', 'site_manager') ORDER BY id DESC");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Staff</h5>
            <a href="staff_add.php" class="btn btn-sm btn-gold"><i class="fa fa-user-plus me-1"></i> Register Staff</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-dark mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Availability</th>
                            <th>Account Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($staff)): foreach($staff as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['phone']) ?></td>
                            <td>
                                <?php if($s['role'] == 'chef'): ?>
                                    <span class="badge bg-danger"><i class="fa fa-fire"></i> Chef</span>
                                <?php elseif($s['role'] == 'server'): ?>
                                    <span class="badge bg-info"><i class="fa fa-concierge-bell"></i> Server</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa fa-user-tie"></i> Site Manager</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($s['availability_status']): ?>
                                    <span class="badge border border-success text-success">Available</span>
                                <?php else: ?>
                                    <span class="badge border border-danger text-danger">Busy</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($s['active_status']): ?>
                                    <span class="text-success"><i class="fa fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fa fa-times-circle"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="staff_add.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                                <?php if($s['active_status']): ?>
                                    <a href="../status/update_status.php?id=<?= $s['id'] ?>&status=0&type=staff" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this staff member?');"><i class="fa fa-ban"></i></a>
                                <?php else: ?>
                                    <a href="../status/update_status.php?id=<?= $s['id'] ?>&status=1&type=staff" class="btn btn-sm btn-outline-success" onclick="return confirm('Activate this staff member?');"><i class="fa fa-check"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No staff members found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
