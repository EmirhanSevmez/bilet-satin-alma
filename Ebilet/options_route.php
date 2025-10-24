<?php 
include __DIR__ . '/cities.php'; 

require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ($user_role != 'company') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}

if ($_POST){
    $route_id = $_POST["route_id"];
    $route = find_route_by_id($route_id,$current_user["company_id"]);
    $tickets = get_ticket_by_route_id($route_id);

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

                        <p><strong>Company:</strong> <?= $company_name ?></p>
                        <p><strong>Departure:</strong> <?= $route['departure_time'] ?></p>
                        <p><strong>Arrival:</strong> <?= $route['arrival_time'] ?></p>
                        <p><strong>Price:</strong> <?= $route['price'] ?> TL</p>
                        <p><strong>Available Seats:</strong> <?= $route['capacity'] ?></p>
                        </div>
                    </div>
        
        </div>
        <?php else: ?>
            <p>No bus routes found for selected cities and date.</p>
        <?php endif; ?>
</div>

 <h3>Change Route</h3>
    <div class="add-route">
        <form action="change_route.php" method="post">
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
            <input type="datetime-local" name="departure_date" required>
        </label>

        <label for="arrival_date">Arrival Date: 
                <input type="datetime-local" name="arrival_date" required>
        </label>
        <label for="price">Price:
            <input type="number" name="price" required>
        </label>
            <label for="capacity">Capacity:
            <input type="number" name="capacity" required>
        </label>
        <input type="hidden" name="route_id" value="<?= $route_id?>">
        <div class="add-route">
            <button type="submit">Change Route</button>
        </div>
        </form>
    </div>

    <?php if ($tickets ): ?> 
<div class="user-ticket-list"> <h2>Tickets:</h2>
    <?php foreach ($tickets as $ticket) : ?>
        <?php if ($ticket['status'] != "canceled") :?>
        <div class="ticket-entry"> 
            <?php
            $route = find_bus_routes_from_id($ticket['trip_id']);
            $company_data = get_bus_company_by_id($route['company_id']);
            ?>
            
            <div class="trip-summary-card">
                <div class="trip-details">
                    <h3><?= $cities[$route['departure_city']] ?> -> <?= $cities[$route['destination_city']] ?></h3>
                    <?php
                    $company_name = $company_data ? $company_data['name'] : 'Unknown Company';
                    ?>
                    <p><strong>Company:</strong> <?= $company_name ?></p>
                    <p><strong>Departure:</strong> <?= $route['departure_time'] ?></p>
                    <p><strong>Arrival:</strong> <?= $route['arrival_time'] ?></p>
                </div>
            </div>
            
            <div class="ticket-action-band">
                
                <p><strong>Status:</strong> <?= $ticket['status'] ?></p>
                <p><strong>Total Price:</strong> <?= $ticket['total_price']?> </p>
                
                <p><strong>Booked Seats:</strong> 
                <?php
                    $booked_seats_data = get_taken_seats_by_ticket_id($ticket['id']);
                    $seat_numbers = array_column($booked_seats_data, 'seat_number');
                    echo implode(', ', $seat_numbers);
                ?>
                </p>

                <?php if ($ticket['status'] == 'active'): ?>
                    <form action="cancel-ticket-company.php" method="post">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <button type="submit">Cancel Ticket</button>
                    </form>

                <?php endif; ?><?php endif; ?>
            </div> </div> <?php endforeach; ?>
</div> <?php endif; ?> 
                