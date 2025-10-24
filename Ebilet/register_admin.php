<?php
require __DIR__ . '/auth/src/bootstrap.php';



$inputs = [];
$errors = [];

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'admin') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}


if (is_post_request()){

    $fields= [
        'fullname' => 'string | required | between 3, 50  ',
        'email' => 'email | required | email | unique: User, email',
        'password' => 'string | required | secure',
        'password2' => 'string | required | same: password',
        'company_name'=> 'string | required',
    ];

    $messages = [
        'password2'=>[
            'required'=> 'Please enter the password again',
            'same'=> 'The password does not match'
        ] ,
     ];

    [$inputs, $errors] = filter($_POST, $fields, $messages);

    if ($errors){
        redirect_with('/Ebilet/',
    [
        'inputs'=> $inputs,
        'errors'=> $errors
    ]);
    }
    if (add_company_admin($inputs['fullname'],$inputs['email'],$inputs['password'], $inputs['company_name'])){
     redirect_to('adminpanel');
     }
}
 else if (is_get_request()) {
    [$inputs, $errors] = session_flash('inputs', 'errors');
}
?>