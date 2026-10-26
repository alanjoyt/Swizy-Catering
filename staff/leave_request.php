<?php require_once('header.php'); 
$dao = new DataAccess();
$staff_id = $_SESSION['user_id'];

if(isset($_POST['btnRequest'])) {
    $date = $_POST['leave_date'];
    $reason = $_POST['reason'];
    
    // Check if date is in the past
    if(strtotime($date) < strtotime(date('Y-m-d'))) {
        $msg = "<div class='alert alert-danger'>You cannot request leave for a past date.</div>";
    } else {
        // Check if leave already requested for this date
        $exists = $dao->count("id", "leave_requests", "staff_id=$staff_id AND leave_date='$date'");
        if($exists > 0) {
            $msg = "<div class='alert alert-warning'>You have already requested leave for this date.</div>";
        } else {
            $dao->insert(array(
                "staff_id" => $staff_id,
                "leave_date" => $date,
                "reason" => $reason,
                "status" => "pending"
            ), "leave_requests");
            $msg = "<div class='alert alert-success'>Leave request submitted successfully.</div>";
        }
    }
}

$leaves = $dao->getData("*", "leave_requests", "staff_id=$staff_id ORDER BY leave_date DESC");
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header border-0 bg-transparent text-gold" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-calendar-alt me-2"></i> Request Time Off</h5>
                </div>
                <div class="card-body">
                    <?= isset($msg) ? $msg : '' ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted">Select Date</label>
                            <input type="date" name="leave_date" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Reason (Optional)</label>
                            <textarea name="reason" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" rows="3"></textarea>
                        </div>
                        <button type="submit" name="btnRequest" class="btn btn-gold w-100"><i class="fa fa-paper-plane"></i> Submit Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header border-0 bg-transparent text-gold d-flex justify-content-between align-items-center" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-history me-2"></i> Leave History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead class="text-uppercase" style="font-size:0.8rem;">
                                <tr>
                                    <th class="ps-4">Date Requested</th>
                                    <th>Leave Date</th>
                                    <th>Reason</th>
                                    <th class="pe-4 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($leaves)): foreach($leaves as $lv): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><small><?= date('d M Y, h:i A', strtotime($lv['created_at'])) ?></small></td>
                                    <td><strong class="text-white"><?= date('d M Y', strtotime($lv['leave_date'])) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($lv['reason']) ?: 'N/A' ?></small></td>
                                    <td class="pe-4 text-end">
                                        <?php if($lv['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fa fa-clock"></i> Pending</span>
                                        <?php elseif($lv['status'] == 'approved'): ?>
                                            <span class="badge bg-success"><i class="fa fa-check"></i> Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fa fa-times"></i> Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-5">No leave requests found.</td></tr>
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
