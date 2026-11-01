<?php
declare(strict_types=1);

/**
 * CINEMORA — Now Showing Page (now-showing.php)
 * Displays active movies currently showing based on database status, release date, and showtimes.
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$nowStr = date('Y-m-d H:i:s');
$todayDate = date('Y-m-d');

$stmt = $pdo->prepare(
    "SELECT m.*,
            (SELECT COUNT(*) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             INNER JOIN cinema_locations c ON c.id = sc.cinema_location_id
             WHERE sh.movie_id = m.id AND sh.status = 'Scheduled'
               AND sh.start_datetime > :now1 AND sc.status = 'Active' AND c.status = 'Active') AS future_showtimes_count,
            (SELECT MIN(sh.base_ticket_price) FROM showtimes sh
             WHERE sh.movie_id = m.id AND sh.status = 'Scheduled' AND sh.start_datetime > :now2) AS min_price
     FROM movies m
     WHERE m.status = 'Active'
       AND (
           m.listing_status = 'Now Showing'
           OR m.release_date <= :today
           OR EXISTS (
               SELECT 1 FROM showtimes sh2
               WHERE sh2.movie_id = m.id AND sh2.status = 'Scheduled' AND sh2.start_datetime > :now3
           )
       )
     ORDER BY m.is_featured DESC, m.release_date DESC"
);
$stmt->execute([
    ':now1'  => $nowStr,
    ':now2'  => $nowStr,
    ':today' => $todayDate,
    ':now3'  => $nowStr,
]);
$movies = $stmt->fetchAll();

$pageTitle = 'Now Showing';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">In Auditoriums Now</span>
        <h1 class="page-hero-title">Now Showing at CINEMORA</h1>
        <p class="page-hero-subtitle">Select a movie below to view scheduled showtimes across Colombo, Kandy, and Galle and reserve your seats.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <?php if (empty($movies)): ?>
            <div class="empty-state">
                <h3>No Movies Currently Showing</h3>
                <p>Check out our Coming Soon page for upcoming premieres.</p>
                <a href="<?= e(url('coming-soon.php')) ?>" class="btn btn-primary">View Coming Soon</a>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php $hasShows = ((int)($movie['future_showtimes_count'] ?? 0) > 0); ?>
                    <article class="movie-card">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="movie-poster-wrap">
                            <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
                            <div class="movie-card-badges">
                                <span class="pill-badge badge-green">Now Showing</span>
                                <span class="pill-badge badge-red"><?= e($movie['age_rating']) ?></span>
                            </div>
                        </a>
                        <div class="movie-card-body">
                            <h2 class="movie-card-title">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>"><?= e($movie['title']) ?></a>
                            </h2>
                            <div class="movie-card-meta">
                                <span><?= e($movie['genre']) ?></span>
                                <span>•</span>
                                <span><?= e(format_duration((int)$movie['duration_minutes'])) ?></span>
                                <span>•</span>
                                <span><?= e($movie['language']) ?></span>
                            </div>
                            <div class="text-muted" style="font-size: 0.82rem; margin-bottom: 14px;">
                                <?= (int)$movie['future_showtimes_count'] ?> Scheduled Showtimes
                                <?php if (!empty($movie['min_price'])): ?>
                                    • <span class="text-gold" style="font-weight: 700;">From <?= e(format_lkr($movie['min_price'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="movie-card-actions <?= $hasShows ? '' : 'single-action' ?>">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="btn btn-secondary btn-sm">View Details</a>
                                <?php if ($hasShows): ?>
                                    <a href="<?= e(url('booking/select-showtime.php?movie_id=' . (int)$movie['id'])) ?>" class="btn btn-primary btn-sm">Book Tickets</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
