<?php


$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">  
    <title><?= $title ?? 'Home' ?></title>
</head> 
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="/Ebilet/">EBilet</a>
        </div>
        <div class="nav-links">
            <?php if($user_logged_in): ?>
                <span>Welcome, <?= htmlspecialchars($current_user['full_name']) ?>! </span>
                <a href="logout">Logout</a>
            <?php else: ?>
                <a href="login">Login</a>
                <a href="register">Register</a>
            <?php endif; ?>
        </div>
    </nav>