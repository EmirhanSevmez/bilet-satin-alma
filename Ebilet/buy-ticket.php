<?php 
include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";

display_all_flash_messages();
$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'user') {
    redirect_with_message("login","You can't access this page.","error") ;
}


$route_id = $_POST['route_id'] ?? $_GET['route_id'] ?? null;

if ($route_id === null) {
    redirect_with_message("/Ebilet/", "Please select a valid route first.", "error");
    exit;
}

$route = find_bus_routes_from_id($route_id);
$company_id = $route['company_id'];
if (empty($route)) {
    redirect_with_message("/Ebilet/", "The selected route was not found.", "error");
    exit;
}

$taken_seats_data = get_taken_seats($route_id);
$taken_seats = array_column($taken_seats_data, 'seat_number');

$_SESSION['temp_seats'] = $_SESSION['temp_seats'] ?? [];
$selected_seats = $_SESSION['temp_seats'][$route_id] ?? [];


if ($_POST && ($_POST['action'] ?? null) == 'apply_coupon') {
    $code = $_POST['code'];
    $coupon = find_valid_coupon_by_code($code,$company_id);
if (!$coupon) {
        unset($_SESSION['active_coupon']);
        redirect_with_message("buy-ticket.php?route_id=" . $route_id, "Invalid, expired, used, or unavailable coupon code.", "error");
        
    } else {
        
        $is_used = check_user_coupons($coupon['id'], $current_user['id']);
        
        if ($is_used) {
            unset($_SESSION['active_coupon']);
            redirect_with_message("buy-ticket.php?route_id=" . $route_id, "This coupon has already been used by you.", "error");
            
        } else {
            
            $_SESSION['active_coupon'] = [
                'code' => $code,
                'discount' => $coupon['discount'],
                'coupon_id'=> $coupon['id'],
            ];
            
            $new_coupon_usage_limit = $coupon['usage_limit'] - 1;
            set_coupon_usage_limit($new_coupon_usage_limit, $coupon['id']);
            create_user_coupon($coupon['id'], $current_user['id']);
            
            redirect_with_message("buy-ticket.php?route_id=" . $route_id, "Coupon applied successfully! Discount: " . $coupon['discount'] . "%", "success");
        }
    }
    }



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_seat'])) {
    $new_seat = (int) $_POST['select_seat'];
    
    $posted_route_id = $_POST['route_id'] ?? null;
    

    
    if ($posted_route_id == $route_id) {
        
        if (in_array($new_seat, $taken_seats)) {
        }
        elseif(in_array($new_seat, $selected_seats)) {
            $selected_seats = array_values(array_diff($selected_seats, [$new_seat]));
        }
        else{
            $selected_seats[] = $new_seat;
        }
        
        $_SESSION['temp_seats'][$route_id] = $selected_seats;
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?route_id=" . $route_id);
        exit;
    }
}

$cost = count($selected_seats) * $route['price'];
if (isset($_SESSION['active_coupon'])) {
    $coupon = $_SESSION['active_coupon'];
    $discount_rate = $coupon['discount'] / 100;

    $applied_discount = $cost * $discount_rate;
    $cost = $cost - $applied_discount;

    $cost = max(0,$cost);
    
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">  
    <link rel="icon" type="image/x-icon" href="/Ebilet/images/favicon.ico"> 
    <title>Buy Ticket</title>
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

    <div class="search-results">
        <h2>Bus Routes</h2>
    <?php if (!empty($route)): ?>
        <div class="routes-list">
                <div class="route-card">
                    <div class="route-info">
                        <h3><?= $cities[$route['departure_city']] ?> -> <?= $cities[$route['destination_city']] ?></h3>
                        <?php
                        $company_data = get_bus_company_by_id($route['company_id']);
                        $company_name = $company_data ? $company_data['name'] : 'Unknown Company';
                        ?>
                            <img src="<?= htmlspecialchars($logo_path) ?>" 
                 alt="<?= htmlspecialchars($company_name) ?> Logo"
                 class="company-logo">
                        <p><strong>Company:</strong> <?= $company_name ?></p>
                        <p><strong>Departure:</strong> <?= format_datetime($route['departure_time']) ?></p>
                        <p><strong>Arrival:</strong> <?= format_datetime($route['arrival_time']) ?></p>
                        <p><strong>Price:</strong> <?= $route['price'] ?> TL</p>
                        <p><strong>Available Seats:</strong> <?= $route['capacity'] ?></p>
                        </div>
                    </div>
                        </div>
        <?php else: ?>
            <p>No bus routes found</p>
        <?php endif; ?>
</div>

<?php if (!empty($route)): ?>
    <div class="bus-layout">
        
        <h3><?= $cities[$route['departure_city']] ?> -> <?= $cities[$route['destination_city']] ?> Select Seat</h3>
        <div class="driver-area">Driver</div>

        <div id="seats-container">
            <?php for ($seat_number = 1; $seat_number <= $route['capacity']; $seat_number++): 
                
                $is_taken = in_array($seat_number, $taken_seats);
                $is_selected = in_array($seat_number, $selected_seats);

                $class = $is_taken ? 'taken' : ($is_selected ? 'selected' : 'empty');
                $disabled = $is_taken ? 'disabled' : '';

                if (($seat_number - 1) % 4 == 2) {
                    echo '<div class="corridor"></div>';
                }
            ?>
            <form method="post" style="display: contents;">
                <input type="hidden" name="route_id" value="<?= $route_id ?>">

                <button type="submit" 
                        name="select_seat" 
                        value="<?= $seat_number ?>" 
                        class="seat <?= $class ?>" 
                        <?= $disabled ?>
                >
                    <?= $seat_number ?>
                </button>
            </form>
            <?php endfor; ?>
        </div>
        




        <div id="selection-summary">
            Selected Seats: <strong><?= empty($selected_seats) ? 'None' : implode(', ', $selected_seats) ?></strong>
        </div>
        <div id="cost">Cost : <strong><?= $cost ?></strong></div>
        <?php if (!empty($selected_seats)): ?>
            <form action="process_payment.php" method="POST">
                <input type="hidden" name="route_id" value="<?= $route_id ?>">
                <input type="hidden" name="seats" value="<?= implode(',', $selected_seats) ?>">
                <button type="submit" class="purchase-button">Proceed to Payment</button>
            </form>
        <?php endif; ?>
<div id="coupon-area">
    <h4>Have a Coupon?</h4>
    <form action="buy-ticket.php?route_id=<?= $route_id ?>" method="POST">
        <input type="text" name="code" placeholder="Enter Coupon Code" required>
        <input type="hidden" name="action" value="apply_coupon">
        <input type="hidden" name="route_id_for_coupon" value="<?= $route_id ?>">
        <button type="submit">Apply</button>
    </form>
</div>
    </div>
    <?php endif; ?>
</body>
</html>