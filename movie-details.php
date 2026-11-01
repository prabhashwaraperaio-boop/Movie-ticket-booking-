<?php
declare(strict_types=1);

/**
 * CINEMORA — Movie Details & Showtimes Page (movie-details.php)
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$movieId = validate_positive_int($_GET['id'] ?? null);

if ($movieId === null) {
    set_flash('error', 'Please select a valid movie to view details.');
    redirect('movies.php');
}

$movieStmt = $pdo->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
$movieStmt->execute([':id' => $movieId]);
$movie = $movieStmt->fetch();

if (!$movie || ($movie['status'] !== 'Active' && !is_admin())) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$nowStr = date('Y-m-d H:i:s');

// Fetch all future scheduled showtimes for this movie in active screens and active cinemas
$showsStmt = $pdo->prepare(
    "SELECT sh.id AS showtime_id, sh.start_datetime, sh.end_datetime, sh.base_ticket_price, sh.status,
            sc.id AS screen_id, sc.screen_name, sc.screen_type,
            c.id AS cinema_id, c.cinema_name, c.city, c.address,
            (SELECT COUNT(*) FROM showtime_seats ss WHERE ss.showtime_id = sh.id AND ss.seat_status = 'Available') AS available_seats,
            (SELECT MIN(ss.ticket_price) FROM showtime_seats ss WHERE ss.showtime_id = sh.id) AS min_seat_price,
            (SELECT MAX(ss.ticket_price) FROM showtime_seats ss WHERE ss.showtime_id = sh.id) AS max_seat_price
     FROM showtimes sh
     INNER JOIN screens sc ON sc.id = sh.screen_id
     INNER JOIN cinema_locations c ON c.id = sc.cinema_location_id
     WHERE sh.movie_id = :movie_id
       AND sh.status = 'Scheduled'
       AND sh.start_datetime > :now
       AND sc.status = 'Active'
       AND c.status = 'Active'
     ORDER BY c.cinema_name ASC, sh.start_datetime ASC"
);
$showsStmt->execute([':movie_id' => $movieId, ':now' => $nowStr]);
$showtimes = $showsStmt->fetchAll();

// Group showtimes by Cinema Location -> Date
$groupedByCinema = [];
$globalMinPrice = null;
$globalMaxPrice = null;

foreach ($showtimes as $sh) {
    $cid = (int)$sh['cinema_id'];
    if (!isset($groupedByCinema[$cid])) {
        $groupedByCinema[$cid] = [
            'cinema_id'   => $cid,
            'cinema_name' => $sh['cinema_name'],
            'city'        => $sh['city'],
            'address'     => $sh['address'],
            'dates'       => [],
        ];
    }
    $dateKey = substr($sh['start_datetime'], 0, 10);
    $groupedByCinema[$cid]['dates'][$dateKey][] = $sh;

    $minP = (float)($sh['min_seat_price'] ?? $sh['base_ticket_price']);
    $maxP = (float)($sh['max_seat_price'] ?? $sh['base_ticket_price']);
    if ($globalMinPrice === null || $minP < $globalMinPrice) {
        $globalMinPrice = $minP;
    }
    if ($globalMaxPrice === null || $maxP > $globalMaxPrice) {
        $globalMaxPrice = $maxP;
    }
}

$isMovieActive = ($movie['status'] === 'Active');
$hasBookableShows = $isMovieActive && !empty($showtimes);

$pageTitle = $movie['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="padding: 56px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;" class="movie-detail-header">
            <div class="glass-card" style="padding: 10px;">
                <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" style="border-radius: 10px; width: 100%;">
            </div>

            <div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;">
                    <span class="pill-badge <?= $movie['listing_status'] === 'Now Showing' ? 'badge-green' : 'badge-gold' ?>">
                        <?= e($movie['listing_status']) ?>
                    </span>
                    <span class="pill-badge badge-red">Rated <?= e($movie['age_rating']) ?></span>
                    <span class="pill-badge badge-muted"><?= e($movie['language']) ?></span>
                    <span class="pill-badge badge-muted"><?= e(format_duration((int)$movie['duration_minutes'])) ?></span>
                </div>

                <h1 class="page-hero-title" style="margin-top: 0;"><?= e($movie['title']) ?></h1>
                <p class="text-gold" style="font-weight: 700; font-size: 0.98rem; margin: 0 0 18px;">
                    <?= e($movie['genre']) ?> • Released <?= e(format_date($movie['release_date'])) ?>
                </p>

                <p class="text-muted" style="font-size: 1.02rem; line-height: 1.75; margin-bottom: 24px;">
                    <?= nl2br(e($movie['description'])) ?>
                </p>

                <div class="glass-card" style="padding: 20px; margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div>
                        <span class="ticket-field-label">Director</span>
                        <span class="ticket-field-value"><?= e($movie['director']) ?></span>
                    </div>
                    <div>
                        <span class="ticket-field-label">Featured Cast</span>
                        <span class="ticket-field-value" style="font-size: 0.92rem;"><?= e($movie['cast']) ?></span>
                    </div>
                    <div>
                        <span class="ticket-field-label">Ticket Price Range</span>
                        <span class="ticket-field-value text-gold">
                            <?php if ($globalMinPrice !== null): ?>
                                <?= e(format_lkr($globalMinPrice)) ?>
                                <?php if ($globalMaxPrice !== null && $globalMaxPrice > $globalMinPrice): ?>
                                    – <?= e(format_lkr($globalMaxPrice)) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                TBA
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                    <?php if ($hasBookableShows): ?>
                        <a href="#showtimes-section" class="btn btn-primary btn-lg">Select Showtime &amp; Book Seats</a>
                        <a href="<?= e(url('booking/select-showtime.php?movie_id=' . (int)$movie['id'])) ?>" class="btn btn-gold btn-lg">Guided Booking Wizard</a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-lg" disabled>
                            <?= $movie['listing_status'] === 'Coming Soon' ? 'Advance Bookings Opening Soon' : 'No Active Showtimes Available' ?>
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($movie['trailer_url'])): ?>
                        <a href="<?= e($movie['trailer_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-lg">
                            ▶ Watch Official Trailer
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Available Cinemas, Theaters & Showtimes -->
<section id="showtimes-section" class="page-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Theater Schedules</span>
                <h2 class="section-title">Available Cinemas &amp; Showtimes</h2>
                <p class="section-subtitle">Click any scheduled showtime below to open that auditorium’s live seating map.</p>
            </div>
        </div>

        <?php if (!$hasBookableShows): ?>
            <div class="empty-state">
                <h3>No Upcoming Showtimes Currently Scheduled for This Title</h3>
                <p>
                    <?php if ($movie['listing_status'] === 'Coming Soon'): ?>
                        <strong><?= e($movie['title']) ?></strong> is scheduled for release on <strong><?= e(format_date($movie['release_date'])) ?></strong>. Showtimes will appear here as soon as bookings open.
                    <?php else: ?>
                        There are no future scheduled screenings for this movie right now. Please explore our other Now Showing presentations.
                    <?php endif; ?>
                </p>
                <a href="<?= e(url('now-showing.php')) ?>" class="btn btn-primary">Browse Now Showing Movies</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($groupedByCinema as $cinemaGroup): ?>
                    <div class="glass-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; margin-bottom: 20px;">
                            <div>
                                <span class="pill-badge badge-gold" style="margin-bottom: 6px;"><?= e($cinemaGroup['city']) ?></span>
                                <h3 style="margin: 0; font-size: 1.3rem;">
                                    <a href="<?= e(url('cinema-details.php?id=' . (int)$cinemaGroup['cinema_id'])) ?>">
                                        <?= e($cinemaGroup['cinema_name']) ?>
                                    </a>
                                </h3>
                                <p class="text-muted" style="margin: 4px 0 0; font-size: 0.86rem;"><?= e($cinemaGroup['address']) ?></p>
                            </div>
                            <a href="<?= e(url('cinema-details.php?id=' . (int)$cinemaGroup['cinema_id'])) ?>" class="btn btn-secondary btn-sm">View Cinema Details</a>
                        </div>

                        <?php foreach ($cinemaGroup['dates'] as $dateStr => $dayShows): ?>
                            <div style="margin-bottom: 18px;">
                                <div style="font-size: 0.86rem; font-weight: 800; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">
                                    📅 <?= e(format_date($dateStr, 'l, d F Y')) ?>
                                </div>
                                <div class="showtime-chips">
                                    <?php foreach ($dayShows as $sh): ?>
                                        <?php $avail = (int)$sh['available_seats']; ?>
                                        <?php if ($avail > 0): ?>
                                            <a href="<?= e(url('booking/seat-selection.php?showtime_id=' . (int)$sh['showtime_id'])) ?>" class="showtime-chip">
                                                <span class="chip-time"><?= e(format_time($sh['start_datetime'])) ?></span>
                                                <span class="chip-hall"><?= e($sh['screen_name']) ?></span>
                                                <span class="chip-price">From <?= e(format_lkr($sh['base_ticket_price'])) ?> • <?= $avail ?> seats left</span>
                                            </a>
                                        <?php else: ?>
                                            <div class="showtime-chip" style="opacity: 0.55; cursor: not-allowed;">
                                                <span class="chip-time"><?= e(format_time($sh['start_datetime'])) ?></span>
                                                <span class="chip-hall"><?= e($sh['screen_name']) ?></span>
                                                <span class="chip-price text-crimson">Housefull / Sold Out</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
