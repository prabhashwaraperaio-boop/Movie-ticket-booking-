<?php
declare(strict_types=1);

/**
 * CINEMORA — Cinema Locations Directory Page (cinemas.php)
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$nowStr = date('Y-m-d H:i:s');
$cityFilter = clean_str($_GET['city'] ?? '');

$where = ["c.status = 'Active'"];
$params = [':now' => $nowStr];

if ($cityFilter !== '') {
    $where[] = "c.city = :city";
    $params[':city'] = $cityFilter;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare(
    "SELECT c.*,
            (SELECT COUNT(*) FROM screens sc WHERE sc.cinema_location_id = c.id AND sc.status = 'Active') AS active_screens_count,
            (SELECT COUNT(DISTINCT sh.movie_id) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             WHERE sc.cinema_location_id = c.id AND sc.status = 'Active'
               AND sh.status = 'Scheduled' AND sh.start_datetime > :now) AS active_movies_count,
            (SELECT COUNT(*) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             WHERE sc.cinema_location_id = c.id AND sc.status = 'Active'
               AND sh.status = 'Scheduled' AND sh.start_datetime > :now2) AS upcoming_shows_count
     FROM cinema_locations c
     WHERE {$whereSql}
     ORDER BY c.cinema_name ASC"
);
$params[':now2'] = $nowStr;
$stmt->execute($params);
$cinemas = $stmt->fetchAll();

$cities = $pdo->query("SELECT DISTINCT city FROM cinema_locations WHERE status = 'Active' ORDER BY city ASC")->fetchAll();

$pageTitle = 'Cinema Locations';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">Our Multiplex Circuit</span>
        <h1 class="page-hero-title">CINEMORA Cinema Locations</h1>
        <p class="page-hero-subtitle">Explore our luxury branches across Sri Lanka, each equipped with multiple custom-designed auditorium halls.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <?php if (count($cities) > 1): ?>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 28px;">
                <a href="<?= e(url('cinemas.php')) ?>" class="btn <?= $cityFilter === '' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All Cities</a>
                <?php foreach ($cities as $cRow): ?>
                    <a href="<?= e(url('cinemas.php?city=' . urlencode($cRow['city']))) ?>"
                       class="btn <?= $cityFilter === $cRow['city'] ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                        <?= e($cRow['city']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cinemas)): ?>
            <div class="empty-state">
                <h3>No Active Cinema Locations Found</h3>
                <p>Please clear the city filter to view all CINEMORA locations.</p>
                <a href="<?= e(url('cinemas.php')) ?>" class="btn btn-primary">Show All Cinemas</a>
            </div>
        <?php else: ?>
            <div class="cinemas-grid">
                <?php foreach ($cinemas as $cinema): ?>
                    <article class="cinema-card">
                        <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>" class="cinema-banner-wrap">
                            <img src="<?= e(resolve_cinema_image_url($cinema['image'])) ?>" alt="<?= e($cinema['cinema_name']) ?>">
                        </a>
                        <div class="cinema-card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px;">
                                <span class="pill-badge badge-gold"><?= e($cinema['city']) ?></span>
                                <span class="pill-badge badge-muted">Code: <?= e($cinema['cinema_code']) ?></span>
                            </div>

                            <h2 class="cinema-card-title">
                                <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>"><?= e($cinema['cinema_name']) ?></a>
                            </h2>

                            <p class="text-muted" style="font-size: 0.9rem; margin: 0 0 14px; flex: 1;">
                                <?= e($cinema['description']) ?>
                            </p>

                            <div class="info-box" style="margin-bottom: 18px; font-size: 0.85rem;">
                                <div><strong>Address:</strong> <?= e($cinema['address']) ?></div>
                                <div><strong>Phone:</strong> <?= e($cinema['phone']) ?></div>
                                <?php if (!empty($cinema['email'])): ?>
                                    <div><strong>Email:</strong> <?= e($cinema['email']) ?></div>
                                <?php endif; ?>
                                <div style="margin-top: 8px; color: var(--accent-gold); font-weight: 700;">
                                    <?= (int)$cinema['active_screens_count'] ?> Active Theaters •
                                    <?= (int)$cinema['active_movies_count'] ?> Movies Showing •
                                    <?= (int)$cinema['upcoming_shows_count'] ?> Upcoming Showtimes
                                </div>
                            </div>

                            <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>" class="btn btn-primary btn-block">
                                View Cinema, Theaters &amp; Showtimes
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
