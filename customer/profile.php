<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$user = $dao->getData("*", "users", "id=$user_id")[0];
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-header text-center py-4">
                <i class="fa fa-user-circle fa-4x mb-3" style="color:var(--primary-gold)"></i>
                <h4 class="brand-font m-0">My Profile</h4>
            </div>
            <div class="card-body p-4 text-center">
                <h5 class="text-white mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted mb-4"><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($user['role']) ?></span></p>
                
                <div class="text-start border border-secondary rounded p-3" style="background: rgba(0,0,0,0.2);">
                    <div class="mb-3">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="text-white"><i class="fa fa-envelope me-2 text-gold" style="color:var(--primary-gold)"></i> <?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="text-white"><i class="fa fa-phone me-2 text-gold" style="color:var(--primary-gold)"></i> <?= htmlspecialchars($user['phone']) ?></span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Member Since</small>
                        <span class="text-white"><i class="fa fa-calendar me-2 text-gold" style="color:var(--primary-gold)"></i> <?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
