<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ($current_user['role'] != 'admin') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}


if ($_POST){
    $company_id = $_POST["company_id"];
    delete_company($company_id);
    redirect_to("adminpanel");
}


?>