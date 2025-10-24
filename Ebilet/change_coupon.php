<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'company') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_POST){
    $code = $_POST['code'];
    $discount = $_POST['discount'];
    $usage_limit = $_POST['usage_limit'];
    $expire_date = $_POST['expire_date'];
    $company_id = $current_user['company_id'];
    $coupon_id = $_POST['coupon_id'];
    change_coupon($code, $discount, $usage_limit, $expire_date, $company_id, $coupon_id);
    redirect_to('companyadmin');
}

display_all_flash_messages();
?>