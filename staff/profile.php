<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$user = $dao->getData("*", "users", "id=$user_id")[0];
$errors = array();

if(isset($_POST['btnUpdatePassword'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "All password fields are required.";
    } elseif(!password_verify($current_password, $user['password'])) {
        $errors[] = "Incorrect current password.";
    } elseif(strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    } elseif($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    }
    
    if(empty($errors)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        if($dao->update(array("password" => $hashed), "users", "id=$user_id")) {
            swalRedirect("Password updated successfully!", "profile.php", "success", "Success");
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <!-- Profile Info Card -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow border-0" style="background:var(--card-bg);">
                <div class="card-header text-center py-4 border-0 bg-transparent">
                    <i class="fa fa-user-shield fa-4x mb-3" style="color:var(--primary-gold)"></i>
                    <h4 class="brand-font m-0 text-gold" style="color:var(--primary-gold);">My Profile</h4>
                </div>
                <div class="card-body px-4 pb-4">
                    <h5 class="text-white text-center mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                    <p class="text-muted text-center mb-4"><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($user['role']) ?></span></p>
                    
                    <div class="text-start border border-secondary border-opacity-20 rounded p-3" style="background: rgba(0,0,0,0.2);">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Email Address</small>
                            <span class="text-white"><i class="fa fa-envelope me-2 text-gold" style="color:var(--primary-gold)"></i> <?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Phone Number</small>
                            <span class="text-white"><i class="fa fa-phone me-2 text-gold" style="color:var(--primary-gold)"></i> <?= htmlspecialchars($user['phone']) ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-1">Member Since</small>
                            <span class="text-white"><i class="fa fa-calendar me-2 text-gold" style="color:var(--primary-gold)"></i> <?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow border-0" style="background:var(--card-bg);">
                <div class="card-header py-4 border-0 bg-transparent text-gold" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-key me-2"></i> Update Password</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger mb-4" style="background: rgba(220,53,69,0.1); border-color: #dc3545; color: #ff6b6b;">
                            <ul class="mb-0">
                                <?php foreach($errors as $err): ?>
                                    <li><?= $err ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" required minlength="6">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" style="background:var(--dark-bg); color:white; border-color:var(--bs-border-color);" required minlength="6">
                        </div>
                        <button type="submit" name="btnUpdatePassword" class="btn btn-gold w-100"><i class="fa fa-save"></i> Save Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
