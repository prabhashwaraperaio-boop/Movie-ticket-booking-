<?php
declare(strict_types=1);

/**
 * CINEMORA — Secure POST Logout Endpoint (logout.php)
 */

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warning', 'Logout requires a secure POST action.');
    redirect('index.php');
}

require_csrf('index.php');

logout_user();
set_flash('success', 'You have been securely signed out of your CINEMORA session.');
redirect('login.php');
