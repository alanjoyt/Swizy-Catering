<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id']) && isset($_GET['booking_id'])) {
    $bs_id = intval($_GET['id']);
    $booking_id = intval($_GET['booking_id']);
    $dao->delete("booking_staff", "id=$bs_id AND booking_id=$booking_id");
    header("Location: ../admin/staff_assign.php?id=$booking_id");
    exit;
}
header('Location: ../admin/bookings.php');
exit;
?>
