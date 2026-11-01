<?php
declare(strict_types=1);

/**
 * CINEMORA — Custom 404 Not Found Page (404.php)
 */

require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = 'Page Not Found (404)';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section" style="padding: 80px 0;">
    <div class="container" style="max-width: 640px;">
        <div class="glass-card text-center" style="padding: 44px 32px;">
            <span class="pill-badge badge-red" style="margin-bottom: 14px;">HTTP 404 • Reel Missing</span>
            <h1 style="font-size: 2.2rem; margin: 0 0 12px;">Scene Not Found</h1>
            <p class="text-muted" style="margin: 0 0 28px;">
                The page, movie, or showtime you requested could not be found or is no longer active in our cinema schedule.
            </p>
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="<?= e(url('index.php')) ?>" class="btn btn-primary">Return to Homepage</a>
                <a href="<?= e(url('movies.php')) ?>" class="btn btn-secondary">Browse Movies</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
