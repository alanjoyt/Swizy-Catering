<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id']) && isset($_GET['status']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);
    $type = $_GET['type'];
    
    if($type === 'customer') {
        $dao->update(array("active_status" => $status), "users", "id=$id AND role='customer'");
        header('Location: ../admin/customers.php');
        exit;
    } elseif($type === 'staff') {
        $dao->update(array("active_status" => $status), "users", "id=$id");
        header('Location: ../admin/staff.php');
        exit;
    }
}
header('Location: ../admin/dashboard.php');
exit;
?>
