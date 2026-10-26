<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    // Get booking details
    $bk = $dao->getData("*", "bookings", "id=$booking_id");
    if(!empty($bk)) {
        $booking = $bk[0];
        
        $update_data = array("payment_status" => "fully_paid");
        $msg = "Booking payment status updated to Fully Paid!";
        
        // If booking is pending, also approve it and auto-allocate staff
        if($booking['booking_status'] == 'pending') {
            $guests = $booking['guest_count'];
            $req_chefs = ceil($guests / 100);
            $req_servers = $guests >= 1000 ? ceil($guests / 100) : ceil($guests / 50);
            $req_sm = $guests >= 1000 ? 3 : 1;
            
            $event_date = $booking['event_date'];
            $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$event_date' AND status='approved')";
            
            // Find available staff
            $available_chefs = $dao->getData("id", "users", "role='chef' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_chefs");
            $available_servers = $dao->getData("id", "users", "role='server' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_servers");
            $available_sm = $dao->getData("id", "users", "role='site_manager' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_sm");
            
            $assigned_count = 0;
            
            // Assign Chefs
            if(is_array($available_chefs)) {
                foreach($available_chefs as $staff) {
                    $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$staff['id'], "assigned_role"=>"chef", "assignment_status"=>"assigned"), "booking_staff");
                    $assigned_count++;
                }
            }
            
            // Assign Servers
            if(is_array($available_servers)) {
                foreach($available_servers as $staff) {
                    $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$staff['id'], "assigned_role"=>"server", "assignment_status"=>"assigned"), "booking_staff");
                    $assigned_count++;
                }
            }
            
            // Assign SM
            if(is_array($available_sm)) {
                foreach($available_sm as $staff) {
                    $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$staff['id'], "assigned_role"=>"site_manager", "assignment_status"=>"assigned"), "booking_staff");
                    $assigned_count++;
                }
            }
            
            $update_data['booking_status'] = 'approved';
            $msg = "Booking marked as Fully Paid, Approved, and $assigned_count staff allocated successfully!";
        }
        
        if($dao->update($update_data, "bookings", "id=$booking_id")) {
            swalRedirect("$msg", "../admin/bookings.php", "success", "Success");
            exit;
        }
    }
}
header('Location: ../admin/bookings.php');
exit;
?>
