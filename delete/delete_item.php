<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Soft delete item
    $dao->update(array("status" => 0), "items", "id=$id");
}
header('Location: ../admin/items.php');
exit;
?>
