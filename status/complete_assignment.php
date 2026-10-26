<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id']) && isset($_GET['user_id'])) {
    $bs_id = intval($_GET['id']);
    $user_id = intval($_GET['user_id']);
    $dao->update(array("assignment_status" => "completed"), "booking_staff", "id=$bs_id AND user_id=$user_id");
}
header('Location: ../staff/dashboard.php');
exit;
?>
