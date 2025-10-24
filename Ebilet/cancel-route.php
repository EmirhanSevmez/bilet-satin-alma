<?php 
include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ( $user_role != 'company') { 
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['route_id'])) {
    redirect_with_message("companyadmin","Invalid request.", "error");
}


$route_id = (int)$_POST['route_id'];
$company_id = $current_user['company_id'];


$tickets = get_ticket_by_route_id($route_id); 

$refunded_count = 0;



if (is_array($tickets) && !empty($tickets)) {
    
    foreach ($tickets as $ticket) {
        
        if ($ticket['status'] === 'active') { 
            
            $user_id_to_refund = (int)$ticket['user_id'];
            if ($user_id_to_refund === 0) { continue; }

            $ticket_owner = find_user_by_id($user_id_to_refund); 

            if ($ticket_owner) {
                

                $new_balance = $ticket_owner['balance'] + $ticket['total_price'];
                set_new_balance($ticket_owner['id'], $new_balance);
                

                cancel_ticket($ticket['id']); 
                
   
                delete_booked_seats($ticket['id']); 

                $refunded_count++;
            }
        }
    }
}

$delete_success = delete_route($route_id, $company_id);


if ($delete_success) {
    redirect_with_message('companyadmin', 
                          "Route deleted successfully. " . $refunded_count . " ticket(s) refunded.", 
                          'success');
} else {
    redirect_with_message('companyadmin', 
                          "ERROR: Route could not be deleted from the database.", 
                          'error');
}
?>