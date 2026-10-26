<?php require_once('header.php'); 
$dao = new DataAccess();

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$staff = null;
if($edit_id) {
    $res = $dao->getData("*", "users", "id=$edit_id AND role IN ('chef', 'server', 'site_manager')");
    if(!empty($res)) $staff = $res[0];
}

$elements = array(
    "name" => $staff ? $staff['name'] : "",
    "email" => $staff ? $staff['email'] : "",
    "phone" => $staff ? $staff['phone'] : "",
    "role" => $staff ? $staff['role'] : "server"
);

$errors = array();

if(isset($_POST['btnSave'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Basic Validation
    if(empty($name) || empty($email) || empty($phone)) {
        $errors[] = "Please fill all required fields.";
    }
    
    if(!$edit_id && empty($password)) {
        $errors[] = "Password is required for new staff.";
    }

    // Check unique email
    $cond = "email='$email'";
    if($edit_id) $cond .= " AND id != $edit_id";
    if($dao->count("id", "users", $cond) > 0) {
        $errors[] = "Email is already registered.";
    }

    if(empty($errors)) {
        $data = array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "role" => $role
        );

        if(!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if($edit_id) {
            if($dao->update($data, "users", "id=$edit_id")) {
                swalRedirect("Staff updated successfully", "staff.php", "success", "Success");
            }
        } else {
            $data['active_status'] = 1;
            $data['availability_status'] = 1;
            if($dao->insert($data, "users")) {
                swalRedirect("Staff registered successfully", "staff.php", "success", "Success");
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= $edit_id ? 'Edit' : 'Register' ?> Staff</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger" style="background: rgba(220,53,69,0.1); border-color: #dc3545; color: #ff6b6b;">
                            <ul class="mb-0">
                                <?php foreach($errors as $err): ?>
                                    <li><?= $err ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($elements['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($elements['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10}" maxlength="10" minlength="10" value="<?= htmlspecialchars($elements['phone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Job Role</label>
                            <select name="role" class="form-control" style="background: var(--card-bg); color: white;" required>
                                <option value="chef" <?= $elements['role']=='chef'?'selected':'' ?>>Chef</option>
                                <option value="server" <?= $elements['role']=='server'?'selected':'' ?>>Server</option>
                                <option value="site_manager" <?= $elements['role']=='site_manager'?'selected':'' ?>>Site Manager</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Password <?= $edit_id ? '(Leave blank to keep current)' : '*' ?></label>
                            <input type="password" name="password" class="form-control" minlength="6" <?= $edit_id ? '' : 'required' ?>>
                        </div>

                        <div class="text-end">
                            <a href="staff.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="btnSave" class="btn btn-gold">Save Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
