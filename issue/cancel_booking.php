<?php
require_once('../config/autoload.php');
$dao = new DataAccess();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if(isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    // Get booking details
    $bk = $dao->getData("*", "bookings", "id=$booking_id AND customer_id=$user_id");
    if(!empty($bk)) {
        $booking = $bk[0];
        if($booking['booking_status'] == 'pending' || $booking['booking_status'] == 'approved') {
            // Check refund logic
            $event_date = strtotime(date('Y-m-d', strtotime($booking['event_date'])));
            $current_date = strtotime(date('Y-m-d'));
            $diff_days = ($event_date - $current_date) / (60 * 60 * 24);
            
            if($diff_days < 3) {
                swalRedirect("Cancellation Failed: Bookings can only be cancelled 3 or more days before the event date.", "../customer/bookings.php", "error", "Error");
                exit;
            }
            
            $status = 'cancelled';
            $msg = "Booking cancelled.";
            
            // Auto remove staff
            $dao->delete("booking_staff", "booking_id=$booking_id");
            
            if($booking['payment_status'] == 'advance_paid' || $booking['payment_status'] == 'fully_paid') {
                $advance = $booking['advance_amount'];
                $fee = $advance * 0.05; // 5% cancellation fee
                $refund = $advance - $fee;
                
                $msg .= " You will be refunded ₹" . number_format($refund, 2) . ". A 5% cancellation fee (₹" . number_format($fee, 2) . ") was deducted.";
                
                // Keep only the 5% fee as advance_amount so it shows as revenue, and mark as refunded
                $dao->update(array("payment_status" => "refunded", "advance_amount" => $fee), "bookings", "id=$booking_id");
            }
            
            $dao->update(array("booking_status" => $status), "bookings", "id=$booking_id");
            swalRedirect("$msg", "../customer/bookings.php", "success", "Success");
            exit;
        } else {
            swalRedirect("Cannot cancel this booking.", "../customer/bookings.php", "error", "Error");
            exit;
        }
    }
}
header('Location: ../customer/bookings.php');
exit;
?>
