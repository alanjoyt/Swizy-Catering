<?php require_once('header.php'); 
$dao = new DataAccess();

if(isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $dao->update(array("status"=>"approved"), "leave_requests", "id=$id");
    swalRedirect("Leave approved.", "manage_leaves.php", "success", "Success");
    exit();
}

if(isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $dao->update(array("status"=>"rejected"), "leave_requests", "id=$id");
    swalRedirect("Leave rejected.", "manage_leaves.php", "success", "Success");
    exit();
}

// Fetch all leave requests
$leaves = $dao->getDataJoin(
    array("leave_requests.*", "users.name as staff_name", "users.role"),
    "leave_requests",
    "1 ORDER BY leave_requests.leave_date DESC",
    array("users" => array("leave_requests.staff_id=users.id", "innerjoin"))
);
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0" style="background:var(--card-bg);">
        <div class="card-header border-0 bg-transparent text-gold d-flex justify-content-between align-items-center" style="color:var(--primary-gold);">
            <h5 class="mb-0"><i class="fa fa-calendar-check me-2"></i> Manage Staff Leaves</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="text-uppercase" style="font-size:0.8rem;">
                        <tr>
                            <th class="ps-4">Staff Member</th>
                            <th>Role</th>
                            <th>Leave Date</th>
                            <th>Reason</th>
                            <th>Requested On</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($leaves)): foreach($leaves as $lv): ?>
                        <tr>
                            <td class="ps-4 text-white fw-bold"><?= htmlspecialchars($lv['staff_name']) ?></td>
                            <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($lv['role']) ?></span></td>
                            <td><strong class="text-gold" style="color:var(--primary-gold);"><?= date('d M Y', strtotime($lv['leave_date'])) ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($lv['reason']) ?: 'N/A' ?></small></td>
                            <td><small class="text-muted"><?= date('d M Y, h:i A', strtotime($lv['created_at'])) ?></small></td>
                            <td>
                                <?php if($lv['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif($lv['status'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if($lv['status'] == 'pending'): ?>
                                    <a href="manage_leaves.php?approve=<?= $lv['id'] ?>" class="btn btn-sm btn-outline-success mb-1" onclick="return confirm('Approve this leave?');"><i class="fa fa-check"></i></a>
                                    <a href="manage_leaves.php?reject=<?= $lv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this leave?');"><i class="fa fa-times"></i></a>
                                <?php else: ?>
                                    <span class="text-muted small">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-5">No leave requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
