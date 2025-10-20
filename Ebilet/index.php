<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";
$today_date = date('Y-m-d');

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
display_all_flash_messages();
update_ticket_expire();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 
    <link rel="icon" type="image/x-icon" href="/Ebilet/images/favicon.ico"> 
    <title>Home</title>
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
      <div class="cities-div">
    <form action="search" method="post">
        <label for="from">From: 
            <select name="from_city" required>
                <?php cities_options($_POST['from_city'] ?? '0')  ?>
            </select>
        </label>
        
        <label for="to">To: 
            <select name="to_city" required>
                <?php cities_options($_POST['to_city'] ?? '0') ?>
            </select>
        </label>
        
        <label for="date">Date: 
                <input type="date" name="date" required min="<?= $today_date ?>">
        </label>

        
        <div class="submit-city">
            <button type="submit">Search Bus</button>
        </div>
    </form>
</div>
</body>
</html>