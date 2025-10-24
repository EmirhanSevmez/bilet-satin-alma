<?php 

require_once __DIR__ ."/auth/src/bootstrap.php";
require __DIR__ . '/register_admin.php';



$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'admin') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

$companies = find_companies();

$coupons = get_all_coupons();

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



<div class="Bus_Company"><h2>Bus Companies</h2>
    <div class="Add_Bus_Company"><h3>Add Bus Company</h3>
        <form action="add_bus_company.php" method="post">
                <label for="company_name">
                    Name:
                    <input type="text" name="company_name" required>
                </label>
                <button type="submit">Create Bus Company</button>
        </form>
    <div class="companies">
        <?php if(!empty($companies)): ?>
            <h2>Company List</h2>
            <?php foreach($companies as $company): ?>
                <div class="company_logo">
    <?php 
    $logo_path = $company['logo_path'];
    ?>
    <img src="<?= htmlspecialchars($logo_path) ?>" 
                 alt="<?= htmlspecialchars($company['name']) ?> Logo"
                 class="company-logo">
</div>
                <div class="company-card">
                    <p><strong>Company Name:<?=$company['name'] ?> </strong></p>
                    <form action="delete_company.php" method="post">
                        <input type="hidden" name="company_id" value="<?= $company['id']?>">
                          <button class="submit" type="submit">Delete</button>
                    </form>
                    <form action="options_company.php" method="post">
                        <input type="hidden" name="company_id" value="<?= $company['id']?>">
                          <button class="submit" type="submit">Options</button>
                    </form>
                </div>
            <?php endforeach; ?>
 </div>
        <?php else : ?>
            <p>No Company Found.</p>
        <?php endif; ?>
</div>
    </div>

<div class="company_admin_manager">
    <h2>Company Admin Manager</h2>
    <div class="add_company_admin">
            <form action="register_admin.php" method="post">
        <h1>Add Company Admin</h1>
        <div>
            <label for="fullname">Name Surname:</label>
            <input type="text" name="fullname" id="fullname" value="<?= $inputs['fullname'] ?? '' ?>"  
                class="<?= error_class($errors, 'fullname')?>">
        </div>
     <div>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= $inputs['email'] ?? '' ?>"
               class="<?= error_class($errors, 'email') ?>">
        <small><?= $errors['email'] ?? '' ?></small>
    </div>

    <div>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" value="<?= $inputs['password'] ?? '' ?>"
               class="<?= error_class($errors, 'password') ?>">
        <small><?= $errors['password'] ?? '' ?></small>
    </div>

    <div>
        <label for="password2">Password Again:</label>
        <input type="password" name="password2" id="password2" value="<?= $inputs['password2'] ?? '' ?>"
               class="<?= error_class($errors, 'password2') ?>">
        <small><?= $errors['password2'] ?? '' ?></small>
    </div>
        <div>
        <label for="company_name">Company Name:</label>
        <input type="text" name="company_name" id="company_name" value="<?= $inputs['company_name'] ?? '' ?>"
               class="<?= error_class($errors, 'company_name') ?>">
        <small><?= $errors['company_name'] ?? '' ?></small>
    </div>

        <button type="submit">Add</button>

    </form>

    </div>
</div>

<div class="coupon">
    <h2>Coupons</h2>
    <div class="add-coupon">
        <h3>Add Coupon</h3>
        <form action="add_coupon.php" method="post">
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
            <label for="company_name">Company Name (Leave blank for all companies.) :<input type="text" name="company_name"></label>
            <button type="submit">Add Coupon</button>
        </form>
    </div>
<div class="coupons">
<?php if(!empty($coupons)): ?>
    <h2>Coupon list</h2>
    <?php foreach($coupons as $coupon): ?>
        <div class coupon-card>
            <div class="coupon-card">
                <div class="coupon-info">
                <p><strong>Code:</strong><?= $coupon['code']?></p>
                <p><strong>Discount:</strong><?= $coupon['discount']?></p>
                <p><strong>Usage Limit:</strong><?= $coupon['usage_limit']?></p>
                <p><strong>Expire Date:</strong><?= $coupon['expire_date']?></p>
                <p><strong>Company:
                    <?= $coupon['company_id'] === null 
                         ? "All" 
                        : (get_bus_company_by_id($coupon['company_id'])['name'] ?? "Unknown Company") 
                    ?>
                </strong></p>
                <form action="cancel-coupon.php" method="post">
                    <input type="hidden" name="coupon_id" value="<?= $coupon['id']?>">
                    <button class="submit" type="submit">Delete</button>
                </form>
                <form action="options_coupon.php " method="post">
                    <input type="hidden" name="coupon_id" value="<?= $coupon['id']?>">
                    <button class="submit" type="submit">Options</button>
</form>
</div>
            </div>
        </div>
        <?php endforeach; ?>
</div>
<?php else: ?>
    <p>No coupons found.</p>
<?php endif; ?>
</div>