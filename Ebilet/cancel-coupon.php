<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";

require_login();

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ($current_user['role'] == 'user') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_POST){
    $coupon_id = $_POST['coupon_id'];
    if ($current_user['role'] == 'company'){
        delete_coupon($coupon_id, $current_user['company_id']);
        redirect_to('companyadmin');
}
    else {
        delete_coupon_admin($coupon_id) ;
        redirect_to('adminpanel');
    }

    }
    
?>