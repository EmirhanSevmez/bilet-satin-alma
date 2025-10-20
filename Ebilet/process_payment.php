<?php 
include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ($current_user['role'] != 'user') {
    redirect_with_message("login","You can't access this page.","error") ;
}

$route_id = $_POST['route_id'] ?? $_GET['route_id'] ?? null;
$taken_seats_data = get_taken_seats($route_id);
$taken_seats = array_column($taken_seats_data, 'seat_number');
$route = find_bus_routes_from_id($route_id);
if ($route_id === null) {
    redirect_with_message("/Ebilet/", "Please select a valid route first.", "error");
    exit;
}

$route = find_bus_routes_from_id($route_id);

if (empty($route)) {
    redirect_with_message("/Ebilet/", "The selected route was not found.", "error");
    exit;
}

if ($_POST){
    $route_id = $_POST["route_id"] ;
}

$selected_seats= $_SESSION['temp_seats'][$route_id] ?? [];

for ($i = 0; $i < count($selected_seats); $i++) {
    if (in_array($selected_seats[$i], $taken_seats)) {
        redirect_with_message('/Ebilet','This seat is taken','error');
        exit;
    }}
$cost = count($selected_seats) * $route['price'];
if (isset($_SESSION['active_coupon'])) {
    $coupon = $_SESSION['active_coupon'];
    $discount_rate = $coupon['discount'] / 100;

    $applied_discount = $cost * $discount_rate;
    $cost = $cost - $applied_discount;

    $cost = max(0,$cost);
    unset($_SESSION['active_coupon']);
}


if ($current_user['balance'] > $cost){
    $ticket_id = create_ticket($route_id,$current_user['id'],$cost);
    
    foreach ($selected_seats as $seat_number) {
    create_booked_seats($ticket_id,$seat_number);

}
    $new_balance = $current_user['balance'] - $cost;
    set_new_balance($current_user['id'],$new_balance);
unset($_SESSION['temp_seats'][$route_id]);
redirect_with_message("myaccount", "Ticket purchase completed successfully!", "success");

}

else {
    redirect_with_message("buy-ticket.php", "Failed to finalize the ticket. Try again.", "error");
}