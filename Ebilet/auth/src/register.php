<?php
require __DIR__ . '/../src/bootstrap.php';



$inputs = [];
$errors = [];


if (is_user_logged_in()) {
    redirect_to('index.php');
}

if (is_post_request()){

    $fields= [
        'fullname' => 'string | required | between 3, 50  ',
        'email' => 'email | required | email | unique: User, email',
        'password' => 'string | required | secure',
        'password2' => 'string | required | same: password',
        'agree' => 'string | required'
    ];

    $messages = [
        'password2'=>[
            'required'=> 'Please enter the password again',
            'same'=> 'The password does not match'
        ] ,
        'agree'=>[
            'required'=> 'You need to agree to the term of services to register'
    ] ];

    [$inputs, $errors] = filter($_POST, $fields, $messages);

    if ($errors){
        redirect_with('/Ebilet/register',
    [
        'inputs'=> $inputs,
        'errors'=> $errors
    ]);
    }
    if (register_user($inputs['fullname'],$inputs['email'],$inputs['password'])){
        redirect_with_message(
            'login',
            'Your account has been created successfully. Please login here.'
       );
     }
}
 else if (is_get_request()) {
    [$inputs, $errors] = session_flash('inputs', 'errors');
}
?>