<?php require_once('header.php'); 
$dao = new DataAccess();

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$item = null;
if($edit_id) {
    $res = $dao->getData("*", "items", "id=$edit_id AND status=1");
    if(!empty($res)) $item = $res[0];
}

$elements = array(
    "name" => isset($_POST['name']) ? $_POST['name'] : ($item ? $item['name'] : ""),
    "category" => isset($_POST['category']) ? $_POST['category'] : ($item ? $item['category'] : "veg"),
    "price_per_head" => isset($_POST['price_per_head']) ? $_POST['price_per_head'] : ($item ? $item['price_per_head'] : ""),
    "description" => isset($_POST['description']) ? $_POST['description'] : ($item ? $item['description'] : "")
);

$errors = array();

if(isset($_POST['btnSave'])) {
    $name = trim($_POST['name']);

    // Check if an item with the same name already exists and is active (status=1)
    $cond = "LOWER(name) = LOWER('" . $dao->escape($name) . "') AND status = 1";
    if($edit_id) {
        $cond .= " AND id != $edit_id";
    }

    $check = $dao->getData("id", "items", $cond);
    if(!empty($check)) {
        $errors[] = "An item with the name '" . htmlspecialchars($name) . "' already exists.";
    }

    if(empty($errors)) {
        $image = $item ? $item['image'] : "";

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
            "name" => $name,
            "category" => $_POST['category'],
            "price_per_head" => $_POST['price_per_head'],
            "description" => $_POST['description'],
            "image" => $image
        );

        if($edit_id) {
            if($dao->update($data, "items", "id=$edit_id")) {
                swalRedirect("Item updated successfully", "items.php", "success", "Success");
            }
        } else {
            $data['status'] = 1;
            if($dao->insert($data, "items")) {
                swalRedirect("Item added successfully", "items.php", "success", "Success");
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
                    <h5 class="mb-0"><?= $edit_id ? 'Edit' : 'Add' ?> Custom Item</h5>
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
                        <div class="mb-3">
                            <label class="form-label text-muted">Item Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($elements['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Category</label>
                            <select name="category" class="form-control" style="background: var(--card-bg); color: white;" required>
                                <option value="veg" <?= $elements['category']=='veg'?'selected':'' ?>>Veg</option>
                                <option value="non-veg" <?= $elements['category']=='non-veg'?'selected':'' ?>>Non-Veg</option>
                                <option value="both" <?= $elements['category']=='both'?'selected':'' ?>>Both</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Price Per Head (₹)</label>
                            <input type="number" step="0.01" min="0" name="price_per_head" class="form-control" required value="<?= htmlspecialchars($elements['price_per_head']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Item Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if($item && $item['image']): ?>
                                <img src="../assets/uploads/<?= $item['image'] ?>" width="100" class="mt-2" style="border:1px solid var(--primary-gold); border-radius:5px;">
                            <?php endif; ?>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Description / Details</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($elements['description']) ?></textarea>
                        </div>

                        <div class="text-end">
                            <a href="items.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="btnSave" class="btn btn-gold">Save Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
