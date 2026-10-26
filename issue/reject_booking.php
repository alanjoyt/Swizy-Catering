<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $dao->update(array("booking_status" => "rejected"), "bookings", "id=$booking_id");
}
header('Location: ../admin/bookings.php');
exit;
?>
