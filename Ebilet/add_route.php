<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'company') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if($_POST){
    $from_city = $_POST['from_city'];
    $to_city = $_POST['to_city'];
    $departure_date = $_POST['departure_date'];
    $arrival_date = $_POST['arrival_date'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $result = add_route(
        $current_user['company_id'], 
        $to_city, 
        $arrival_date, 
        $departure_date, 
        $from_city, 
        $price,
        $capacity
    );

    if ($result > 0) {
        redirect_with_message("companyadmin","Route added successfully.","success") ;
    } else {
        redirect_with_message("companyadmin","ERROR: Route could not be added. Check if data is valid.","error") ;
    }
}

?>
