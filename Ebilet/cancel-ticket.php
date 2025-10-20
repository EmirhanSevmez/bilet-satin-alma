<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ( $current_user['role'] != 'user') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}



if ($_POST){
    $ticket_id = $_POST["ticket_id"];
    $ticket = get_ticket_by_ticket_id($ticket_id);
    $route = find_bus_routes_from_id($ticket["trip_id"]);
    $departure_time_str = $route['departure_time'];

    $departure_timestamp = strtotime($departure_time_str);

    $current_timestamp = time();

    $time_difference = $departure_timestamp - $current_timestamp;

    $one_hour_in_seconds = 3600;
    if ($time_difference < $one_hour_in_seconds){
        redirect_with_message('myaccount','Error expired','error');
    }
    if ($ticket['user_id'] == $current_user['id']) {
        delete_booked_seats($ticket_id);
        cancel_ticket( $ticket_id );
        $balance = $current_user['balance'] + $ticket['total_price'];
        set_new_balance($current_user['id'],$balance);
        redirect_with_message('myaccount','Canceled Successfully','success');
    }
    else {
        redirect_with_message('myaccount','Error','error');
    }
}


?>