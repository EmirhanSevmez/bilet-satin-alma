<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'company') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_POST){
    $from_city = $_POST["from_city"];
    $to_city = $_POST["to_city"];
    $departure_date = $_POST["departure_date"];
    $arrival_date = $_POST["arrival_date"];
    $price = $_POST["price"];
    $capacity = $_POST["capacity"];
    $route_id = $_POST["route_id"];
    $company_id = $current_user["company_id"];
    change_route($from_city, $to_city, $departure_date, $arrival_date, $price, $capacity, $route_id, $company_id);
    redirect_to('companyadmin');
}

display_all_flash_messages();
?>