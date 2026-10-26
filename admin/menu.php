<?php require_once('header.php'); 
$dao = new DataAccess();

$packages = $dao->getData("*", "menu_packages", "status=1 ORDER BY id DESC");


?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Menu Packages</h5>
            <a href="menu_add.php" class="btn btn-sm btn-gold"><i class="fa fa-plus me-1"></i> Add New Package</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-dark mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Package Name</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Price (₹)</th>
                            <th>Guest Limit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($packages)): foreach($packages as $pkg): ?>
                        <tr>
                            <td>
                                <img src="../assets/uploads/<?= htmlspecialchars($pkg['image']) ?>" alt="<?= htmlspecialchars($pkg['package_name']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--primary-gold);">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($pkg['package_name']) ?></strong><br>
                                <small class="text-muted" style="font-size:0.8rem;">
                                    <?php 
                                    $items = json_decode($pkg['menu_items'], true); 
                                    if(is_array($items)) {
                                        echo implode(', ', array_slice($items, 0, 8)) . (count($items) > 8 ? '...' : '');
                                    }
                                    ?>
                                </small>
                            </td>
                            <td>
                                <?php if($pkg['package_type'] == 'luxury'): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa fa-crown"></i> Luxury</span>
                                <?php elseif($pkg['package_type'] == 'premium'): ?>
                                    <span class="badge bg-primary">Premium</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($pkg['category'] == 'veg'): ?>
                                    <span class="text-success"><i class="fa fa-leaf"></i> Veg</span>
                                <?php elseif($pkg['category'] == 'non-veg'): ?>
                                    <span class="text-danger"><i class="fa fa-drumstick-bite"></i> Non-Veg</span>
                                <?php else: ?>
                                    <span class="text-info">Both</span>
                                <?php endif; ?>
                            </td>
                            <td>₹<?= number_format($pkg['price'], 2) ?></td>
                            <td><?= $pkg['guest_limit'] > 0 ? $pkg['guest_limit'] : 'Unlimited' ?></td>
                            <td>
                                <a href="menu_add.php?edit=<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                                <a href="../delete/delete_menu.php?id=<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this package?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No menu packages found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
