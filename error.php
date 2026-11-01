<?php
declare(strict_types=1);

/**
 * CINEMORA — Safe General Error Page (error.php)
 */

require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'System Notice';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section" style="padding: 80px 0;">
    <div class="container" style="max-width: 640px;">
        <div class="glass-card text-center" style="padding: 44px 32px;">
            <span class="pill-badge badge-gold" style="margin-bottom: 14px;">CINEMORA System Notice</span>
            <h1 style="font-size: 2rem; margin: 0 0 12px;">We Encountered an Unexpected Issue</h1>
            <p class="text-muted" style="margin: 0 0 28px;">
                Your request could not be completed at this moment. No unauthorized changes or duplicate bookings were recorded.
            </p>
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="<?= e(url('index.php')) ?>" class="btn btn-primary">Back to Homepage</a>
                <a href="<?= e(url('contact.php')) ?>" class="btn btn-secondary">Contact Support</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
