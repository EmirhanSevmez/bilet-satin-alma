<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";
require_login();

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

if ($current_user['role'] == 'user') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_POST){
    $coupon_id = $_POST["coupon_id"];
    if ($current_user['role'] == 'company'){
        $coupon = find_coupon_by_id($coupon_id,$current_user["company_id"]);
    }
    else {
        $coupon = find_coupon_by_id_admin($coupon_id);
    }
}

display_all_flash_messages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">  
    <link rel="icon" type="image/x-icon" href="/Ebilet/images/favicon.ico"> 
    <title>Company Admin Panel</title>
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

<div class="coupons">
<?php if(!empty($coupon)): ?>
    <h2>Coupon list</h2>
        <div class coupon-card>
            <div class="coupon-card">
                <div class="coupon-info">
                <p><strong>Code:</strong><?= $coupon['code']?></p>
                <p><strong>Discount:</strong><?= $coupon['discount']?></p>
                <p><strong>Usage Limit:</strong><?= $coupon['usage_limit']?></p>
                <p><strong>Expire Date:</strong><?= $coupon['expire_date']?></p>

</div>
</div>
</div>

<div class="coupon">
    <h2>Coupon</h2>
    <div class="add-coupon">
        <h3>Change Coupon</h3>
        <form action="change_coupon.php" method="post">
            <label for="code">Code:
                <input type="text" name="code" required>
            </label>
            <label for="discount">Discount:
                <input type="number" name="discount" required>
            </label>
            <label for="usage_limit">Usage Limit:
                <input type="number" name="usage_limit" required>
            </label>
            <label for="expire_date">Expire Date:
                <input type="datetime-local" name="expire_date">
            </label>
            <input type="hidden" name="coupon_id" value="<?= $coupon['id']?>">
            <button type="submit">Change Coupon</button>
        </form>
    </div>

                    <?php else: ?>
    <p>No coupons found.</p>
<?php endif; ?>