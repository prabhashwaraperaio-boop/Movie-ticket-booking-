<?php
declare(strict_types=1);

/**
 * CINEMORA — Cinema Details, Auditoriums & Theater-Specific Showtimes (cinema-details.php)
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$cinemaId = validate_positive_int($_GET['id'] ?? null);

if ($cinemaId === null) {
    set_flash('error', 'Please select a valid cinema location.');
    redirect('cinemas.php');
}

$cinemaStmt = $pdo->prepare("SELECT * FROM cinema_locations WHERE id = :id LIMIT 1");
$cinemaStmt->execute([':id' => $cinemaId]);
$cinema = $cinemaStmt->fetch();

if (!$cinema || ($cinema['status'] !== 'Active' && !is_admin())) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// Fetch all active theaters belonging to this cinema with capacity & category breakdown
$screensStmt = $pdo->prepare(
    "SELECT sc.*,
            (SELECT COUNT(*) FROM seats s WHERE s.screen_id = sc.id AND s.is_active = 1) AS active_seats_count,
            (SELECT COUNT(*) FROM seats s WHERE s.screen_id = sc.id AND s.is_active = 1 AND s.seat_category = 'Standard') AS std_count,
            (SELECT COUNT(*) FROM seats s WHERE s.screen_id = sc.id AND s.is_active = 1 AND s.seat_category = 'Premium') AS prem_count,
            (SELECT COUNT(*) FROM seats s WHERE s.screen_id = sc.id AND s.is_active = 1 AND s.seat_category = 'VIP') AS vip_count
     FROM screens sc
     WHERE sc.cinema_location_id = :cid AND sc.status = 'Active'
     ORDER BY sc.screen_code ASC"
);
$screensStmt->execute([':cid' => $cinemaId]);
$screens = $screensStmt->fetchAll();

$nowStr = date('Y-m-d H:i:s');

// Fetch future scheduled showtimes for this cinema
$showsStmt = $pdo->prepare(
    "SELECT sh.id AS showtime_id, sh.start_datetime, sh.end_datetime, sh.base_ticket_price,
            sc.id AS screen_id, sc.screen_name, sc.screen_type,
            m.id AS movie_id, m.title AS movie_title, m.genre, m.duration_minutes,
            m.language, m.age_rating, m.poster,
            (SELECT COUNT(*) FROM showtime_seats ss WHERE ss.showtime_id = sh.id AND ss.seat_status = 'Available') AS available_seats
     FROM showtimes sh
     INNER JOIN screens sc ON sc.id = sh.screen_id
     INNER JOIN movies m ON m.id = sh.movie_id
     WHERE sc.cinema_location_id = :cid
       AND sc.status = 'Active'
       AND m.status = 'Active'
       AND sh.status = 'Scheduled'
       AND sh.start_datetime > :now
     ORDER BY m.title ASC, sh.start_datetime ASC"
);
$showsStmt->execute([':cid' => $cinemaId, ':now' => $nowStr]);
$showtimes = $showsStmt->fetchAll();

// Group showtimes by Movie
$moviesInCinema = [];
foreach ($showtimes as $sh) {
    $mid = (int)$sh['movie_id'];
    if (!isset($moviesInCinema[$mid])) {
        $moviesInCinema[$mid] = [
            'movie_id'         => $mid,
            'title'            => $sh['movie_title'],
            'genre'            => $sh['genre'],
            'duration_minutes' => (int)$sh['duration_minutes'],
            'language'         => $sh['language'],
            'age_rating'       => $sh['age_rating'],
            'poster'           => $sh['poster'],
            'showtimes'        => [],
        ];
    }
    $moviesInCinema[$mid]['showtimes'][] = $sh;
}

$pageTitle = $cinema['cinema_name'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 36px; align-items: center;" class="hero-grid">
            <div>
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px;">
                    <span class="pill-badge badge-gold"><?= e($cinema['city']) ?> Branch</span>
                    <span class="pill-badge badge-muted">Code: <?= e($cinema['cinema_code']) ?></span>
                </div>
                <h1 class="page-hero-title"><?= e($cinema['cinema_name']) ?></h1>
                <p class="page-hero-subtitle" style="margin-bottom: 20px;"><?= e($cinema['description']) ?></p>

                <div class="info-box">
                    <div><strong>Address:</strong> <?= e($cinema['address']) ?>, <?= e($cinema['city']) ?></div>
                    <div><strong>Box Office Hotline:</strong> <?= e($cinema['phone']) ?></div>
                    <?php if (!empty($cinema['email'])): ?>
                        <div><strong>Branch Email:</strong> <?= e($cinema['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="glass-card" style="padding: 10px;">
                <img src="<?= e(resolve_cinema_image_url($cinema['image'])) ?>" alt="<?= e($cinema['cinema_name']) ?>" style="border-radius: 10px; width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- Active Auditoriums / Halls at this Cinema -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Auditoriums &amp; Seating Tiers</span>
                <h2 class="section-title">Theaters Inside <?= e($cinema['cinema_name']) ?></h2>
                <p class="section-subtitle">Each hall has an independent seating arrangement with custom aisles and seat categories.</p>
            </div>
        </div>

        <?php if (empty($screens)): ?>
            <div class="empty-state">
                <h3>No Active Auditoriums Listed</h3>
                <p>Auditoriums for this branch are currently under configuration.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 22px;">
                <?php foreach ($screens as $sc): ?>
                    <div class="glass-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="pill-badge badge-red"><?= e($sc['screen_code']) ?></span>
                            <span class="pill-badge badge-gold"><?= e($sc['screen_type']) ?></span>
                        </div>
                        <h3 style="margin: 0 0 8px; font-size: 1.2rem;"><?= e($sc['screen_name']) ?></h3>
                        <p class="text-muted" style="font-size: 0.88rem; margin: 0 0 14px;">
                            <?= e($sc['description'] ?: 'Custom-engineered cinema auditorium.') ?>
                        </p>
                        <div class="info-box" style="font-size: 0.84rem;">
                            <div><strong>Total Capacity:</strong> <?= (int)$sc['active_seats_count'] ?> Active Seats (<?= (int)$sc['rows_count'] ?> Rows × <?= (int)$sc['columns_count'] ?> Columns Grid)</div>
                            <div style="margin-top: 4px; color: var(--text-secondary);">
                                Standard: <strong><?= (int)$sc['std_count'] ?></strong> •
                                Premium: <strong><?= (int)$sc['prem_count'] ?></strong> •
                                VIP: <strong><?= (int)$sc['vip_count'] ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Movies & Showtimes at this Cinema -->
<section class="page-section page-section-alt">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Branch Schedule</span>
                <h2 class="section-title">Movies &amp; Showtimes at <?= e($cinema['cinema_name']) ?></h2>
                <p class="section-subtitle">Select any showtime below to view that theater’s exact seating map and reserve tickets.</p>
            </div>
        </div>

        <?php if (empty($moviesInCinema)): ?>
            <div class="empty-state">
                <h3>No Upcoming Showtimes Currently Scheduled at This Branch</h3>
                <p>Explore our other cinema locations or browse all movies.</p>
                <a href="<?= e(url('movies.php')) ?>" class="btn btn-primary">Browse All Movies</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($moviesInCinema as $mGroup): ?>
                    <div class="glass-card" style="display: grid; grid-template-columns: 150px 1fr; gap: 24px; align-items: start;">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$mGroup['movie_id'])) ?>">
                            <img src="<?= e(resolve_poster_url($mGroup['poster'])) ?>" alt="<?= e($mGroup['title']) ?>" style="border-radius: 8px;">
                        </a>
                        <div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                                <span class="pill-badge badge-red"><?= e($mGroup['age_rating']) ?></span>
                                <span class="pill-badge badge-muted"><?= e($mGroup['language']) ?></span>
                                <span class="pill-badge badge-muted"><?= e(format_duration($mGroup['duration_minutes'])) ?></span>
                            </div>
                            <h3 style="margin: 0 0 6px; font-size: 1.35rem;">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$mGroup['movie_id'])) ?>"><?= e($mGroup['title']) ?></a>
                            </h3>
                            <p class="text-muted" style="font-size: 0.88rem; margin: 0 0 16px;"><?= e($mGroup['genre']) ?></p>

                            <div class="showtime-chips">
                                <?php foreach ($mGroup['showtimes'] as $sh): ?>
                                    <?php $avail = (int)$sh['available_seats']; ?>
                                    <?php if ($avail > 0): ?>
                                        <a href="<?= e(url('booking/seat-selection.php?showtime_id=' . (int)$sh['showtime_id'])) ?>" class="showtime-chip">
                                            <span class="chip-time"><?= e(format_datetime($sh['start_datetime'], 'd M • h:i A')) ?></span>
                                            <span class="chip-hall"><?= e($sh['screen_name']) ?></span>
                                            <span class="chip-price">From <?= e(format_lkr($sh['base_ticket_price'])) ?> (<?= $avail ?> avail)</span>
                                        </a>
                                    <?php else: ?>
                                        <div class="showtime-chip" style="opacity: 0.55; cursor: not-allowed;">
                                            <span class="chip-time"><?= e(format_datetime($sh['start_datetime'], 'd M • h:i A')) ?></span>
                                            <span class="chip-hall"><?= e($sh['screen_name']) ?></span>
                                            <span class="chip-price text-crimson">Sold Out</span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
