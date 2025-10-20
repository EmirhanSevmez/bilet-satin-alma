<?php 
include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ( $current_user['role'] != 'company') { 
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ticket_id'])) {
    redirect_with_message("companyadmin","Invalid request.", "error");
}


$ticket_id = (int)$_POST['ticket_id'];
$company_id = $current_user['company_id'];


$ticket = get_ticket_by_ticket_id($ticket_id); 

$refunded_count = 0;



if (is_array($ticket) && !empty($ticket)) {
    
 
        
        if ($ticket['status'] === 'active') { 
            
            $user_id_to_refund = (int)$ticket['user_id'];

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
redirect_to("companyadmin");



?>