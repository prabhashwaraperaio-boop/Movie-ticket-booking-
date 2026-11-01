<?php
declare(strict_types=1);

/**
 * CINEMORA — Coming Soon Page (coming-soon.php)
 * Displays upcoming movies based on database status and future release dates.
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$todayDate = date('Y-m-d');
$nowStr = date('Y-m-d H:i:s');

$stmt = $pdo->prepare(
    "SELECT m.*,
            (SELECT COUNT(*) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             INNER JOIN cinema_locations c ON c.id = sc.cinema_location_id
             WHERE sh.movie_id = m.id AND sh.status = 'Scheduled'
               AND sh.start_datetime > :now AND sc.status = 'Active' AND c.status = 'Active') AS future_showtimes_count
     FROM movies m
     WHERE m.status = 'Active'
       AND (m.listing_status = 'Coming Soon' OR m.release_date > :today)
     ORDER BY m.release_date ASC"
);
$stmt->execute([':now' => $nowStr, ':today' => $todayDate]);
$movies = $stmt->fetchAll();

$pageTitle = 'Coming Soon';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">Upcoming Releases</span>
        <h1 class="page-hero-title">Coming Soon to CINEMORA</h1>
        <p class="page-hero-subtitle">Preview anticipated feature films arriving soon at our Colombo, Kandy, and Galle branches.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <?php if (empty($movies)): ?>
            <div class="empty-state">
                <h3>No Upcoming Releases Listed Right Now</h3>
                <p>Explore our current lineup of Now Showing presentations.</p>
                <a href="<?= e(url('now-showing.php')) ?>" class="btn btn-primary">Browse Now Showing</a>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php $hasAdvanceShows = ((int)($movie['future_showtimes_count'] ?? 0) > 0); ?>
                    <article class="movie-card">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="movie-poster-wrap">
                            <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
                            <div class="movie-card-badges">
                                <span class="pill-badge badge-gold">Coming Soon</span>
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
                            <div class="text-gold" style="font-size: 0.83rem; font-weight: 700; margin-bottom: 14px;">
                                Premieres <?= e(format_date($movie['release_date'])) ?>
                            </div>
                            <div class="movie-card-actions <?= $hasAdvanceShows ? '' : 'single-action' ?>">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="btn btn-secondary btn-sm">View Details</a>
                                <?php if ($hasAdvanceShows): ?>
                                    <a href="<?= e(url('booking/select-showtime.php?movie_id=' . (int)$movie['id'])) ?>" class="btn btn-primary btn-sm">Advance Booking</a>
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
