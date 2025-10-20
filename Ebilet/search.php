<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";

if ($_POST) {
    $from_city = $_POST['from_city'];
    $to_city = $_POST['to_city'];
    $date = $_POST['date'];
    
    $routes = find_bus_routes($from_city, $to_city,$date);
}





$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;



if (empty($from_city) || empty($to_city) || empty($date)) {
    redirect_with_message("/Ebilet/", "Please fill in all search fields.", "error");
    exit;
}

if ($from_city == $to_city) {
    redirect_with_message("/Ebilet/", "Departure and destination cities cannot be the same.", "error");
    exit;
}

$selected_timestamp = strtotime($date);

$today_start_timestamp = strtotime(date('Y-m-d')); 

if ($selected_timestamp < $today_start_timestamp) {
    redirect_with_message("/Ebilet/", "You cannot search for routes in the past. Please select today or a future date.", "error");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">  
    <link rel="icon" type="image/x-icon" href="/Ebilet/images/favicon.ico"> 
    <title>Search Result</title>
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
    <?php if (!empty($routes)): ?>
        <div class="routes-list">
            <?php foreach ($routes as $route): ?>
                <div class="route-card">
                    <div class="route-info">
                        <h3><?= $cities[$route['departure_city']] ?> -> <?= $cities[$route['destination_city']] ?></h3>
                        <?php
                        $company_data = get_bus_company_by_id($route['company_id']);
                        $company_name = $company_data ? $company_data['name'] : 'Unknown Company';
                        ?>

                        <p><strong>Company:</strong> <?= $company_name ?></p>
                        <p><strong>Departure:</strong> <?= format_datetime($route['departure_time']) ?></p>
                        <p><strong>Arrival:</strong> <?= format_datetime($route['arrival_time']) ?></p>
                        <p><strong>Price:</strong> <?= $route['price'] ?> TL</p>
                        <p><strong>Available Seats:</strong> <?= $route['capacity'] ?></p>
                        </div>
                    <form action="buy-ticket.php" method="post">
                        <input type="hidden" name="route_id" value="<?= $route['id']?>">
                          <button class="book-btn" type="submit">Book Now</button>
                    </form>
                    </div>
                
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p>No bus routes found for selected cities and date.</p>
        <?php endif; ?>
</div>
</body>
</html>