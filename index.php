<?php
declare(strict_types=1);

/**
 * CINEMORA — Homepage (index.php)
 * Multi-Cinema Movie Ticket Booking Web Application
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$nowStr = date('Y-m-d H:i:s');

// 1. Fetch Featured Movie (or first Now Showing movie)
$featuredStmt = $pdo->prepare(
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
     ORDER BY m.is_featured DESC, m.listing_status ASC, m.release_date DESC
     LIMIT 1"
);
$featuredStmt->execute([':now1' => $nowStr, ':now2' => $nowStr]);
$featuredMovie = $featuredStmt->fetch();

// 2. Fetch Now Showing Movies (Active movies with listing_status = 'Now Showing' or future showtimes)
$nowShowingStmt = $pdo->prepare(
    "SELECT m.*,
            (SELECT COUNT(*) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             INNER JOIN cinema_locations c ON c.id = sc.cinema_location_id
             WHERE sh.movie_id = m.id AND sh.status = 'Scheduled'
               AND sh.start_datetime > :now1 AND sc.status = 'Active' AND c.status = 'Active') AS future_showtimes_count,
            (SELECT MIN(sh.base_ticket_price) FROM showtimes sh
             WHERE sh.movie_id = m.id AND sh.status = 'Scheduled' AND sh.start_datetime > :now2) AS min_price
     FROM movies m
     WHERE m.status = 'Active' AND m.listing_status = 'Now Showing'
     ORDER BY m.is_featured DESC, m.release_date DESC
     LIMIT 6"
);
$nowShowingStmt->execute([':now1' => $nowStr, ':now2' => $nowStr]);
$nowShowingMovies = $nowShowingStmt->fetchAll();

// 3. Fetch Coming Soon Movies
$comingSoonStmt = $pdo->prepare(
    "SELECT m.*
     FROM movies m
     WHERE m.status = 'Active' AND m.listing_status = 'Coming Soon'
     ORDER BY m.release_date ASC
     LIMIT 4"
);
$comingSoonStmt->execute();
$comingSoonMovies = $comingSoonStmt->fetchAll();

// 4. Fetch Active Cinema Locations with Theater & Showtime Counts
$cinemasStmt = $pdo->prepare(
    "SELECT c.*,
            (SELECT COUNT(*) FROM screens sc WHERE sc.cinema_location_id = c.id AND sc.status = 'Active') AS active_screens_count,
            (SELECT COUNT(*) FROM showtimes sh
             INNER JOIN screens sc ON sc.id = sh.screen_id
             WHERE sc.cinema_location_id = c.id AND sc.status = 'Active'
               AND sh.status = 'Scheduled' AND sh.start_datetime > :now) AS upcoming_shows_count
     FROM cinema_locations c
     WHERE c.status = 'Active'
     ORDER BY c.id ASC"
);
$cinemasStmt->execute([':now' => $nowStr]);
$cinemas = $cinemasStmt->fetchAll();

// 5. Distinct genres for quick search bar
$genresRows = $pdo->query("SELECT DISTINCT genre FROM movies WHERE status = 'Active' ORDER BY genre ASC")->fetchAll();

// 6. Platform KPI counters for Hero bar
$totalCinemasCount = count($cinemas);
$totalScreensCount = (int)$pdo->query("SELECT COUNT(*) FROM screens WHERE status = 'Active'")->fetchColumn();
$totalMoviesCount  = (int)$pdo->query("SELECT COUNT(*) FROM movies WHERE status = 'Active'")->fetchColumn();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Showcase -->
<section class="hero-showcase">
    <div class="hero-bg" style="background-image: url('<?= e(asset_url('assets/images/hero/hero-banner.jpg')) ?>');" aria-hidden="true"></div>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-grid">
        <div>
            <span class="hero-eyebrow">★ Sri Lanka’s Premier Multi-Cinema Network</span>
            <h1 class="hero-title">
                Experience Movies.<br>
                <span>Reserve Your Moment.</span>
            </h1>
            <p class="hero-lead">
                Discover blockbusters and acclaimed Sri Lankan &amp; international cinema across our luxury multiplexes in Colombo, Kandy, and Galle. Choose your exact auditorium seat on interactive theater maps.
            </p>
            <div class="hero-cta-group">
                <a href="<?= e(url('movies.php')) ?>" class="btn btn-primary btn-lg">Explore Movies</a>
                <a href="<?= e(url('now-showing.php')) ?>" class="btn btn-gold btn-lg">Book Tickets Now</a>
                <a href="<?= e(url('cinemas.php')) ?>" class="btn btn-secondary btn-lg">Our Cinema Locations</a>
            </div>
            <div class="hero-stats-bar">
                <div class="hero-stat-item">
                    <strong><?= $totalCinemasCount ?> Branches</strong>
                    <span>Colombo • Kandy • Galle</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?= $totalScreensCount ?> Auditoriums</strong>
                    <span>Custom Tiered Layouts</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?= $totalMoviesCount ?> Active Titles</strong>
                    <span>Laser 4K &amp; Surround Sound</span>
                </div>
            </div>
        </div>

        <?php if ($featuredMovie): ?>
            <div class="glass-card" style="border-color: var(--border-gold); padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span class="pill-badge badge-gold">Featured Presentation</span>
                    <span class="pill-badge badge-red"><?= e($featuredMovie['age_rating']) ?></span>
                </div>
                <div style="display: grid; grid-template-columns: 135px 1fr; gap: 18px; align-items: center;">
                    <a href="<?= e(url('movie-details.php?id=' . (int)$featuredMovie['id'])) ?>" style="border-radius: 8px; overflow: hidden;">
                        <img src="<?= e(resolve_poster_url($featuredMovie['poster'])) ?>" alt="<?= e($featuredMovie['title']) ?>">
                    </a>
                    <div>
                        <h2 style="margin: 0 0 6px; font-size: 1.3rem; font-weight: 800;">
                            <a href="<?= e(url('movie-details.php?id=' . (int)$featuredMovie['id'])) ?>"><?= e($featuredMovie['title']) ?></a>
                        </h2>
                        <div class="text-muted" style="font-size: 0.84rem; margin-bottom: 10px;">
                            <?= e($featuredMovie['genre']) ?> • <?= e(format_duration((int)$featuredMovie['duration_minutes'])) ?> • <?= e($featuredMovie['language']) ?>
                        </div>
                        <p class="text-muted" style="font-size: 0.85rem; margin: 0 0 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= e($featuredMovie['description']) ?>
                        </p>
                        <?php if (!empty($featuredMovie['min_price'])): ?>
                            <div style="font-size: 0.83rem; color: var(--accent-gold); font-weight: 700; margin-bottom: 12px;">
                                Tickets from <?= e(format_lkr($featuredMovie['min_price'])) ?>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="<?= e(url('movie-details.php?id=' . (int)$featuredMovie['id'])) ?>" class="btn btn-secondary btn-sm">View Details</a>
                            <?php if ((int)$featuredMovie['future_showtimes_count'] > 0): ?>
                                <a href="<?= e(url('booking/select-showtime.php?movie_id=' . (int)$featuredMovie['id'])) ?>" class="btn btn-primary btn-sm">Book Tickets</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Quick Search & Filter Bar -->
<section class="page-section" style="padding-top: 36px; padding-bottom: 20px;">
    <div class="container">
        <form method="get" action="<?= e(url('movies.php')) ?>" class="filter-bar" aria-label="Quick movie search and filter">
            <div>
                <label for="home_q" class="form-label">Search Movie Title</label>
                <input type="text" id="home_q" name="q" class="form-control" placeholder="e.g. Crimson Monsoon, Sigiriya...">
            </div>
            <div>
                <label for="home_genre" class="form-label">Genre / Category</label>
                <select id="home_genre" name="genre" class="form-select">
                    <option value="">All Genres</option>
                    <?php foreach ($genresRows as $gr): ?>
                        <option value="<?= e($gr['genre']) ?>"><?= e($gr['genre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="home_status" class="form-label">Release Status</label>
                <select id="home_status" name="listing_status" class="form-select">
                    <option value="">All Movies</option>
                    <option value="Now Showing">Now Showing</option>
                    <option value="Coming Soon">Coming Soon</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary btn-block">Find Movies</button>
            </div>
        </form>
    </div>
</section>

<!-- Now Showing Movies Section -->
<section class="page-section reveal-on-scroll" style="padding-top: 24px;">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">In Auditoriums Today</span>
                <h2 class="section-title">Now Showing</h2>
                <p class="section-subtitle">Select a feature presentation and reserve your exact theater seats in LKR.</p>
            </div>
            <a href="<?= e(url('now-showing.php')) ?>" class="btn btn-secondary">View All Now Showing →</a>
        </div>

        <?php if (empty($nowShowingMovies)): ?>
            <div class="empty-state">
                <h3>No Active Presentations Currently Scheduled</h3>
                <p>Check back shortly or browse our upcoming releases.</p>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($nowShowingMovies as $movie): ?>
                    <?php $hasShows = ((int)($movie['future_showtimes_count'] ?? 0) > 0); ?>
                    <article class="movie-card">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="movie-poster-wrap">
                            <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
                            <div class="movie-card-badges">
                                <span class="pill-badge badge-green">Now Showing</span>
                                <span class="pill-badge badge-gold"><?= e($movie['age_rating']) ?></span>
                            </div>
                        </a>
                        <div class="movie-card-body">
                            <h3 class="movie-card-title">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>"><?= e($movie['title']) ?></a>
                            </h3>
                            <div class="movie-card-meta">
                                <span><?= e($movie['genre']) ?></span>
                                <span>•</span>
                                <span><?= e(format_duration((int)$movie['duration_minutes'])) ?></span>
                                <span>•</span>
                                <span><?= e($movie['language']) ?></span>
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

<!-- Coming Soon Section -->
<section class="page-section page-section-alt reveal-on-scroll">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Upcoming Premieres</span>
                <h2 class="section-title">Coming Soon to CINEMORA</h2>
                <p class="section-subtitle">Preview anticipated feature releases scheduled across our cinema branches.</p>
            </div>
            <a href="<?= e(url('coming-soon.php')) ?>" class="btn btn-secondary">Explore Coming Soon →</a>
        </div>

        <?php if (empty($comingSoonMovies)): ?>
            <div class="empty-state">
                <h3>New Premieres Being Announced Soon</h3>
                <p>Stay tuned for upcoming titles across our Colombo, Kandy, and Galle branches.</p>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($comingSoonMovies as $movie): ?>
                    <article class="movie-card">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="movie-poster-wrap">
                            <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
                            <div class="movie-card-badges">
                                <span class="pill-badge badge-gold">Coming Soon</span>
                                <span class="pill-badge badge-muted"><?= e($movie['age_rating']) ?></span>
                            </div>
                        </a>
                        <div class="movie-card-body">
                            <h3 class="movie-card-title">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>"><?= e($movie['title']) ?></a>
                            </h3>
                            <div class="movie-card-meta">
                                <span><?= e($movie['genre']) ?></span>
                                <span>•</span>
                                <span><?= e(format_duration((int)$movie['duration_minutes'])) ?></span>
                                <span>•</span>
                                <span><?= e($movie['language']) ?></span>
                            </div>
                            <div class="text-gold" style="font-size: 0.81rem; font-weight: 700; margin-bottom: 14px;">
                                Releases <?= e(format_date($movie['release_date'])) ?>
                            </div>
                            <div class="movie-card-actions single-action">
                                <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Cinema Locations Highlights -->
<section class="page-section reveal-on-scroll">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Multi-Cinema Circuit</span>
                <h2 class="section-title">Our Luxury Cinema Locations</h2>
                <p class="section-subtitle">Each branch houses multiple auditoriums with bespoke seating arrangements.</p>
            </div>
            <a href="<?= e(url('cinemas.php')) ?>" class="btn btn-secondary">All Cinema Branches →</a>
        </div>

        <div class="cinemas-grid">
            <?php foreach ($cinemas as $cinema): ?>
                <article class="cinema-card">
                    <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>" class="cinema-banner-wrap">
                        <img src="<?= e(resolve_cinema_image_url($cinema['image'])) ?>" alt="<?= e($cinema['cinema_name']) ?>" loading="lazy">
                    </a>
                    <div class="cinema-card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span class="pill-badge badge-gold"><?= e($cinema['city']) ?></span>
                            <span class="pill-badge badge-muted"><?= (int)$cinema['active_screens_count'] ?> Theaters • <?= (int)$cinema['upcoming_shows_count'] ?> Shows</span>
                        </div>
                        <h3 class="cinema-card-title">
                            <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>"><?= e($cinema['cinema_name']) ?></a>
                        </h3>
                        <p class="text-muted" style="font-size: 0.88rem; margin: 0 0 12px;"><?= e($cinema['address']) ?></p>
                        <p class="text-muted" style="font-size: 0.86rem; margin: 0 0 18px; flex: 1;">
                            <?= e($cinema['description']) ?>
                        </p>
                        <a href="<?= e(url('cinema-details.php?id=' . (int)$cinema['id'])) ?>" class="btn btn-primary btn-sm">View Cinema &amp; Showtimes</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About & Concierge CTA Section -->
<section class="page-section page-section-alt reveal-on-scroll">
    <div class="container">
        <div class="glass-card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; align-items: center;">
            <div>
                <span class="section-eyebrow">The CINEMORA Standard</span>
                <h2 class="section-title" style="margin-bottom: 12px;">Crafted for True Movie Lovers</h2>
                <p class="text-muted" style="margin: 0 0 18px;">
                    Every CINEMORA theater hall is individually engineered with custom sightlines, Standard, Premium, and VIP recliner categories, dedicated wheelchair-accessible spaces, and transparent LKR pricing with convenient <strong>Pay at Cinema</strong> reservation.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="<?= e(url('about.php')) ?>" class="btn btn-secondary">About CINEMORA</a>
                    <a href="<?= e(url('contact.php')) ?>" class="btn btn-primary">Contact Box Office Concierge</a>
                </div>
            </div>
            <div class="info-box">
                <h3 style="margin: 0 0 10px; font-size: 1.05rem;">How Your Reservation Works</h3>
                <ol style="margin: 0; padding-left: 20px; color: var(--text-secondary); font-size: 0.9rem; line-height: 1.8;">
                    <li>Select your movie, cinema branch, auditorium hall, and showtime.</li>
                    <li>Pick your exact seats on that hall’s live seating map.</li>
                    <li>Confirm your reservation and receive an instant digital ticket code.</li>
                    <li>Present your ticket at the cinema box office to complete payment in LKR.</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
