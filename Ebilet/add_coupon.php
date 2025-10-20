<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";
require_login();

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ($current_user['role'] == 'user') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}


if ($_POST){
    $code = $_POST['code'];
    $discount = $_POST['discount'];
    $usage_limit = $_POST['usage_limit'];
    $expire_date = $_POST['expire_date'];
    if($current_user['role'] =='company'){
        $company_id = $current_user['company_id'];
    add_coupon($code, $discount, $usage_limit, $expire_date, $company_id);
    redirect_to(url: 'companyadmin');
}
elseif($current_user['role'] == 'admin'){
    if($_POST['company_name']){
    $company_id = get_company_id_by_name($_POST['company_name'])['id'];
    add_coupon($code, $discount, $usage_limit,$expire_date, $company_id);
        redirect_to(url: 'adminpanel');

}
else{
    add_coupon_admin($code, $discount, $usage_limit, $expire_date);
            redirect_to(url: 'adminpanel');

}
}}
?>
