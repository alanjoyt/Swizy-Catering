<?php require_once('header.php'); 
$dao = new DataAccess();

if(isset($_POST['btnUpload'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    // File upload logic
    $target_dir = "../uploads/";
    if(!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $dao->insert(array(
            "media_type" => "image",
            "media_path" => $file_name,
            "title" => $title,
            "section" => "gallery"
        ), "media");
        $msg = "<div class='alert alert-success'>Image uploaded to gallery successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error uploading image.</div>";
    }
}

if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $img = $dao->getData("media_path", "media", "id=$id");
    if(!empty($img)) {
        $path = "../uploads/" . $img[0]['media_path'];
        if(file_exists($path)) unlink($path);
        $dao->delete("media", "id=$id");
        swalRedirect("Image deleted.", "gallery.php", "success", "Success");
        exit();
    }
}

$gallery = $dao->getData("*", "media", "section='gallery' ORDER BY id DESC");
?>

<div class="container-fluid py-4">
    <?= isset($msg) ? $msg : '' ?>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent text-gold" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-upload me-2"></i> Upload to Gallery</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label text-muted">Image File</label>
                            <input type="file" name="image" class="form-control bg-dark text-white border-secondary" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Title / Event Name</label>
                            <input type="text" name="title" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <input type="hidden" name="description" value="">
                        <button type="submit" name="btnUpload" class="btn btn-gold w-100">Upload Image</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0" style="background:var(--card-bg);">
                <div class="card-header bg-transparent text-gold" style="color:var(--primary-gold);">
                    <h5 class="mb-0"><i class="fa fa-images me-2"></i> Manage Gallery</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if(!empty($gallery)): foreach($gallery as $img): ?>
                        <div class="col-md-4">
                            <div class="position-relative group">
                                <img src="../uploads/<?= $img['media_path'] ?>" class="img-fluid rounded" style="height:150px; width:100%; object-fit:cover; border: 1px solid var(--primary-gold);">
                                <div class="position-absolute bottom-0 w-100 p-2" style="background:rgba(0,0,0,0.7); border-bottom-left-radius:5px; border-bottom-right-radius:5px;">
                                    <h6 class="text-white mb-0 text-truncate" style="font-size:0.85rem;"><?= htmlspecialchars($img['title']) ?></h6>
                                </div>
                                <a href="gallery.php?delete=<?= $img['id'] ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="return confirm('Delete this image?');"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="col-12 text-center text-muted py-5">No images in gallery yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
