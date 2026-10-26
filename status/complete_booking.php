<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    // Update booking status to completed
    if($dao->update(array("booking_status" => "completed"), "bookings", "id=$booking_id")) {
        // Also update booking staff assignment status to completed
        $dao->update(array("assignment_status" => "completed"), "booking_staff", "booking_id=$booking_id AND assignment_status='assigned'");
        
        swalRedirect("Booking and assigned duties marked as completed successfully!", "../admin/bookings.php", "success", "Success");
        exit;
    }
}
header('Location: ../admin/bookings.php');
exit;
?>
