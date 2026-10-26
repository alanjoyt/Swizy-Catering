<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Soft delete menu package
    $dao->update(array("status" => 0), "menu_packages", "id=$id");
}
header('Location: ../admin/menu.php');
exit;
?>
