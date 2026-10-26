<?php
require_once('../config/autoload.php');
$dao = new DataAccess();

if(isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    // Check if already approved
    $bk = $dao->getData("*", "bookings", "id=$booking_id");
    if(!empty($bk) && $bk[0]['booking_status'] == 'pending') {
        $guests = $bk[0]['guest_count'];
        
        // Auto allocation rules
        $req_chefs = ceil($guests / 350);
        $req_servers = ceil($guests / 250);
        $req_sm = $guests >= 1000 ? 2 : 1;
        
        $event_date = $bk[0]['event_date'];
        
        $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$event_date' AND status='approved') 
                       AND id NOT IN (SELECT user_id FROM booking_staff JOIN bookings ON booking_staff.booking_id = bookings.id WHERE bookings.event_date = '$event_date' AND bookings.booking_status IN ('approved', 'completed') AND booking_staff.assignment_status = 'assigned')";
        
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
        
        // Approve booking
        $dao->update(array("booking_status"=>"approved"), "bookings", "id=$booking_id");
        
        swalRedirect("Booking Approved! Successfully auto-allocated $assigned_count staff members based on $guests guests.", "../admin/bookings.php", "success", "Success");
        exit;
    }
}
header('Location: ../admin/bookings.php');
exit;
?>
