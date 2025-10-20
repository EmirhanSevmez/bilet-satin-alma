<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/libs/database.php';
require_once __DIR__ . '/libs/helpers.php';
require_once __DIR__ . '/libs/flash.php';
require_once __DIR__ . '/libs/sanitization.php';
require_once __DIR__ . '/libs/validation.php';
require_once __DIR__ . '/libs/filter.php';
require_once __DIR__ . '/inc/auth.php';