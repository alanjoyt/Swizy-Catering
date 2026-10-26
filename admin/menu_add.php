<?php require_once('header.php'); 
$dao = new DataAccess();

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$pkg = null;
if($edit_id) {
    $res = $dao->getData("*", "menu_packages", "id=$edit_id AND status=1");
    if(!empty($res)) $pkg = $res[0];
}

$elements = array(
    "package_name" => isset($_POST['package_name']) ? $_POST['package_name'] : ($pkg ? $pkg['package_name'] : ""),
    "package_type" => isset($_POST['package_type']) ? $_POST['package_type'] : ($pkg ? $pkg['package_type'] : "normal"),
    "category" => isset($_POST['category']) ? $_POST['category'] : ($pkg ? $pkg['category'] : "both"),
    "price" => isset($_POST['price']) ? $_POST['price'] : ($pkg ? $pkg['price'] : ""),
    "guest_limit" => isset($_POST['guest_limit']) ? $_POST['guest_limit'] : ($pkg ? $pkg['guest_limit'] : "0"),
    "menu_items" => isset($_POST['menu_items']) ? $_POST['menu_items'] : ($pkg ? json_decode($pkg['menu_items'], true) : array("Welcome Drink", "Starter", "Main Course", "Dessert"))
);

$errors = array();

if(isset($_POST['btnSave'])) {
    $package_name = trim($_POST['package_name']);
    $package_type = $_POST['package_type'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $guest_limit = $_POST['guest_limit'];
    
    // Check if package name already exists case-insensitively for active packages (status = 1)
    $cond = "LOWER(package_name) = LOWER('" . $dao->escape($package_name) . "') AND status = 1";
    if($edit_id) {
        $cond .= " AND id != $edit_id";
    }
    
    $check = $dao->getData("id", "menu_packages", $cond);
    if(!empty($check)) {
        $errors[] = "A package with the name '" . htmlspecialchars($package_name) . "' already exists.";
    }

    if(empty($errors)) {
        // JSON encode items list (ensuring no duplicate items)
        $items = array();
        $seen = array();
        if(isset($_POST['menu_items']) && is_array($_POST['menu_items'])) {
            foreach($_POST['menu_items'] as $item) {
                $trimmed = trim($item);
                if($trimmed != "") {
                    $lower = strtolower($trimmed);
                    if(!in_array($lower, $seen)) {
                        $seen[] = $lower;
                        $items[] = $trimmed;
                    }
                }
            }
        }
        $menu_items_json = json_encode($items);

        $image = $pkg ? $pkg['image'] : "default_menu.jpg";

        if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
            // Upload file
            if(!is_dir("../assets/uploads")) {
                mkdir("../assets/uploads", 0777, true);
            }
            $target_dir = "../assets/uploads/";
            $image = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }

        $data = array(
            "package_name" => $package_name,
            "package_type" => $package_type,
            "category" => $category,
            "price" => $price,
            "guest_limit" => $guest_limit,
            "image" => $image,
            "menu_items" => $menu_items_json
        );

        if($edit_id) {
            if($dao->update($data, "menu_packages", "id=$edit_id")) {
                swalRedirect("Updated successfully", "menu.php", "success", "Success");
            }
        } else {
            $data['status'] = 1;
            if($dao->insert($data, "menu_packages")) {
                swalRedirect("Added successfully", "menu.php", "success", "Success");
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= $edit_id ? 'Edit' : 'Add' ?> Menu Package</h5>
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
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Package Name</label>
                                <input type="text" name="package_name" class="form-control" required value="<?= htmlspecialchars($elements['package_name']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Type</label>
                                <select name="package_type" class="form-control" style="background: var(--card-bg);">
                                    <option value="normal" <?= $elements['package_type']=='normal'?'selected':'' ?>>Normal</option>
                                    <option value="premium" <?= $elements['package_type']=='premium'?'selected':'' ?>>Premium</option>
                                    <option value="luxury" <?= $elements['package_type']=='luxury'?'selected':'' ?>>Luxury</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Category</label>
                                <select name="category" class="form-control" style="background: var(--card-bg);">
                                    <option value="veg" <?= $elements['category']=='veg'?'selected':'' ?>>Veg</option>
                                    <option value="non-veg" <?= $elements['category']=='non-veg'?'selected':'' ?>>Non-Veg</option>
                                    <option value="both" <?= $elements['category']=='both'?'selected':'' ?>>Both</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Price (₹)</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" required value="<?= htmlspecialchars($elements['price']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Guest Limit (0 = Unlimited)</label>
                                <input type="number" min="0" name="guest_limit" class="form-control" required value="<?= htmlspecialchars($elements['guest_limit']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Package Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if($pkg && $pkg['image']): ?>
                                    <img src="../assets/uploads/<?= $pkg['image'] ?>" width="100" class="mt-2" style="border:1px solid var(--primary-gold); border-radius:5px;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4 mt-3">
                            <label class="form-label text-gold" style="color: var(--primary-gold);"><i class="fa fa-list"></i> Menu Items</label>
                            <div id="menu-items-container">
                                <?php foreach($elements['menu_items'] as $item): ?>
                                <div class="input-group mb-2 menu-item-row">
                                    <input type="text" name="menu_items[]" class="form-control" value="<?= htmlspecialchars($item) ?>" required>
                                    <button type="button" class="btn btn-outline-danger remove-item"><i class="fa fa-times"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-item" class="btn btn-sm btn-outline-gold mt-2"><i class="fa fa-plus"></i> Add Item</button>
                        </div>

                        <div class="text-end">
                            <a href="menu.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="btnSave" class="btn btn-gold">Save Package</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('menu-items-container');
    const row = document.createElement('div');
    row.className = 'input-group mb-2 menu-item-row';
    row.innerHTML = `
        <input type="text" name="menu_items[]" class="form-control" placeholder="Enter item name" required>
        <button type="button" class="btn btn-outline-danger remove-item"><i class="fa fa-times"></i></button>
    `;
    container.appendChild(row);
});

document.getElementById('menu-items-container').addEventListener('click', function(e) {
    if(e.target.classList.contains('remove-item') || e.target.parentElement.classList.contains('remove-item')) {
        const row = e.target.closest('.menu-item-row');
        if(document.querySelectorAll('.menu-item-row').length > 1) {
            row.remove();
        } else {
            alert('At least one item is required.');
        }
    }
});
</script>

<?php require_once('footer.php'); ?>
