<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'admin') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}
if($_POST){
    $company_id = $_POST["company_id"];


    $company = get_bus_company_by_id("$company_id");

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">  
    <link rel="icon" type="image/x-icon" href="/Ebilet/images/favicon.ico"> 
    <title>Admin Panel</title>
</head> 
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="/Ebilet/">EBilet</a>
        </div>
        <div class="nav-links">
            <?php if (!$user_logged_in) : ?>
                <a href="login">Login</a>
                <a href="register">Register</a>
            
            <?php elseif($current_user['role'] == 'user'): ?>
                <span>Welcome, <?= htmlspecialchars($current_user['full_name']) ?>! </span>
                <a href="logout">Logout</a>
                <a href="myaccount">Myaccount</a>
            <?php elseif($current_user['role'] == 'company'): ?>
                <span>Welcome, <?= htmlspecialchars($current_user['full_name']) ?>! </span>
                <a href="logout">Logout</a>
                <a href="companyadmin">Myaccount</a>
            <?php elseif($current_user['role'] == 'admin'): ?>
                <span>Welcome, <?= htmlspecialchars($current_user['full_name']) ?>! </span>
                <a href="logout">Logout</a>
                <a href="adminpanel">Myaccount</a>
            <?php else: ?>
                <a href="login">Login</a>
                <a href="register">Register</a>
            <?php endif; ?>
        </div>
    </nav>

        <div class="companie">
        <?php if(!empty($company)): ?>
            <h2>Company</h2>
                <div class="company-card">
                    <p><strong>Company Name:<?=$company['name'] ?> </strong></p>
                </div>
                <div class="Add_Bus_Company"><h3>Change Bus Company</h3>
        <form action="change_bus_company.php" method="post">
                <label for="company_name">
                    <input type="text" name="company_name" required>
                </label>
                 <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                <button type="submit">Change Bus Company Name</button>
        </form>
             <form action="upload.php" method="post" enctype="multipart/form-data">
                 Company Logo:
                <input type="file" name="fileToUpload" id="fileToUpload" required>
                <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                <input type="submit" value="Upload Image" name="submit">
            </form>
    </div>
 </div>
        <?php else : ?>
            <p>No Company Found.</p>
        <?php endif; ?>

