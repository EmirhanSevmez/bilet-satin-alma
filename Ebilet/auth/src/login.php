<?php
$inputs = [];
$errors = [];

if (is_user_logged_in()) {
    redirect_to('index.php');
}

if (is_post_request()) {
    [$inputs, $errors] = filter($_POST, [
        'email' => 'string | required',
        'password'=> 'string | required'
    ]);

    if ($errors) {
        redirect_with('login.php', ['errors'=> $errors , 'inputs'=> $inputs]);
}

// if login fails
if (!login($inputs['email'], $inputs['password'])){
    $errors['login'] = 'Invalid email or password';

    redirect_with('/Ebilet/login', ['errors'=> $errors ,''=> $inputs]);    
}

// if login success

redirect_to('index.php');
}
else if (is_get_request()) {
    [$errors, $inputs] = session_flash('errors', 'inputs');
}