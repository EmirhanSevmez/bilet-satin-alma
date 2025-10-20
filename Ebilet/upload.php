<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";
display_all_flash_messages();

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;


if ( $current_user['role'] != 'admin') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

$target_dir = "images/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$new_file_name = $_POST['company_id'] . "." . $imageFileType;

$target_file = $target_dir . $new_file_name;

if ($_POST){
    $company_id = $_POST["company_id"];
    change_company_path($company_id, $target_file);
}


if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
    $uploadOk = 1;
} else {
    $uploadOk = 0;
    redirect_with_message( "/Ebilet/","Your file was not uploaded. It's not a image file","error") ;
}}


if ($_FILES["fileToUpload"]["size"] > 100000000) {
    $uploadOk = 0;
    redirect_with_message( "/Ebilet/","Your file was not uploaded. Size error.","error") ;

}
if ($imageFileType != "jpg"&& $imageFileType != "png"&& $imageFileType != "jpeg"){
    $uploadOk = 0;
    redirect_with_message( "/Ebilet/","Your file was not uploaded. Type error.","error") ;

}
if ($uploadOk == 0) {
    redirect_with_message( "/Ebilet/","Your file was not uploaded.","error") ;
}
else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"] , $target_file)) {
        redirect_with_message( "adminpanel","Your file has been uploaded ","success") ;

}
else {
    redirect_with_message( "/Ebilet/","Your file was not uploaded","error") ;
}
}
?>