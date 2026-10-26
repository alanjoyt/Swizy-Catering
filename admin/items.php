<?php require_once('header.php'); 
$dao = new DataAccess();



$items = $dao->getData("*", "items", "status=1 ORDER BY id DESC");
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Custom Menu Items</h5>
            <a href="item_add.php" class="btn btn-sm btn-gold"><i class="fa fa-plus me-1"></i> Add New Item</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-dark mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price (₹) / Head</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($items)): foreach($items as $itm): ?>
                        <tr>
                            <td>
                                <?php if(!empty($itm['image'])): ?>
                                    <img src="../assets/uploads/<?= htmlspecialchars($itm['image']) ?>" alt="<?= htmlspecialchars($itm['name']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--primary-gold);">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; border-radius: 8px; background: rgba(212,175,55,0.1); border: 1px dashed var(--primary-gold); display: flex; align-items: center; justify-content: center;"><i class="fa fa-utensils text-gold"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($itm['name']) ?></strong></td>
                            <td>
                                <?php if($itm['category'] == 'veg'): ?>
                                    <span class="text-success"><i class="fa fa-leaf"></i> Veg</span>
                                <?php elseif($itm['category'] == 'non-veg'): ?>
                                    <span class="text-danger"><i class="fa fa-drumstick-bite"></i> Non-Veg</span>
                                <?php else: ?>
                                    <span class="text-info">Both</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-gold" style="color:var(--primary-gold);">₹<?= number_format($itm['price_per_head'], 2) ?></span></td>
                            <td style="max-width:300px;"><small class="text-muted"><?= htmlspecialchars($itm['description']) ?></small></td>
                            <td>
                                <a href="item_add.php?edit=<?= $itm['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                                <a href="../delete/delete_item.php?id=<?= $itm['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No individual items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
