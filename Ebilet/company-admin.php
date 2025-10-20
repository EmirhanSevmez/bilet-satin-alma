<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$routes = find_bus_routes_from_company($current_user["company_id"]);

if ($current_user['role'] != 'company') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}
$company_data = get_bus_company_by_id($current_user['company_id']);
$coupons = get_coupons_by_id($current_user['company_id']);
display_all_flash_messages();

$today_datetime = date('Y-m-d\TH:i')
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

<h2><?= $company_data['name'] ?></h2>
<div class="company_logo">
    <?php 
    $logo_path = $company_data['logo_path'];
    ?>
    <img src="<?= htmlspecialchars($logo_path) ?>" 
                 alt="<?= htmlspecialchars($company_data['name']) ?> Logo" 
                 class="company-logo">
</div>
<div class="Crud-system">
    <h3>Add Route</h3>
    <div class="add-route">
        <form action="addroute" method="post">
             <label for="from">From: 
            <select name="from_city" required>
                <?php cities_options($_POST['from_city'] ?? '0') ?>
            </select>
        </label>
        
        <label for="to">To: 
            <select name="to_city" required>
                <?php cities_options($_POST['to_city'] ?? '0') ?>
            </select>
        </label>

            <label for="departure_date">Departure Date:
            <input type="datetime-local" name="departure_date" required min="<?= $today_datetime ?>">
        </label>

        <label for="arrival_date">Arrival Date: 
                <input type="datetime-local" name="arrival_date" required min="<?= $today_datetime ?>">
        </label>

        <label for="price">Price:
            <input type="number" name="price" required>
        </label>
        <label for="capacity">Capacity:
            <input type="capacity" name="capacity" required>
</label>
        <div class="add-route">
            <button type="submit">Add Route</button>
        </div>
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
                <input type="datetime-local" name="expire_date" min="<?= $today_datetime ?>">
            </label>
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

    <?php if (!empty($routes)): ?>
        <div class="routes-list">
            <h2>Route list</h2>
            <?php foreach ($routes as $route): ?>
                <div class="route-card">
                    <div class="route-info">
                        <h3><?= $cities[$route['departure_city']] ?> -> <?= $cities[$route['destination_city']] ?></h3>
                        <?php
                        $company_name = $company_data ? $company_data['name'] : 'Unknown Company';
                        ?>

                        <p><strong>Company:</strong> <?= $company_name ?></p>
                        <p><strong>Departure:</strong> <?= format_datetime($route['departure_time']) ?></p>
                        <p><strong>Arrival:</strong> <?= format_datetime($route['arrival_time']) ?></p>
                        <p><strong>Price:</strong> <?= $route['price'] ?> TL</p>
                        <p><strong>Available Seats:</strong> <?= $route['capacity'] ?></p></div>
                    <form action="cancel-route.php" method="post">
                        <input type="hidden" name="route_id" value="<?= $route['id']?>">
                          <button class="submit" type="submit">Delete</button>
                    </form>
                    <form action="options_route.php" method="post">
                        <input type="hidden" name="route_id" value="<?= $route['id']?>">
                          <button class="submit" type="submit">Options</button>
                    </form>
                    </div>
        <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p>No bus routes found for selected cities and date.</p>
        <?php endif; ?>
    </div>