<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

$tickets = get_tickets($current_user['id']);

if($current_user['role'] != 'user') {
   redirect_with_message("/Ebilet/","You can't access this page.","error") ;
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
    <title>Profile</title>
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

    <div class="user-credit">
    <p>Credit: <?= $current_user['balance'] ?></p>
</div>

<?php if ($tickets ): ?> 
<div class="user-ticket-list">
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
                    <p><strong>Departure:</strong> <?= format_datetime($route['departure_time']) ?></p>
                    <p><strong>Arrival:</strong> <?= format_datetime($route['arrival_time']) ?></p>
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
                    <form action="cancel-ticket.php" method="post">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <button type="submit">Cancel Ticket</button>
                    </form>
                    <form action="ticket-pdf.php" method="post">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <button type="submit">Download PDF</button>
                    </form>
                <?php endif; ?><?php endif; ?>
            </div> </div> <?php endforeach; ?>
</div> <?php endif; ?> 
                
