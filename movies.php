<?php
declare(strict_types=1);

/**
 * CINEMORA — Movies Catalogue Page (movies.php)
 * Search by title, filter by genre, language, and status, sort, and paginate.
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$nowStr = date('Y-m-d H:i:s');

$searchQuery   = clean_str($_GET['q'] ?? '');
$genreFilter   = clean_str($_GET['genre'] ?? '');
$langFilter    = clean_str($_GET['language'] ?? '');
$statusFilter  = clean_str($_GET['listing_status'] ?? '');
$sortBy        = clean_str($_GET['sort'] ?? 'release_desc');
$currentPage   = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 8;

$where = ["m.status = 'Active'"];
$params = [];

if ($searchQuery !== '') {
    $where[] = "(m.title LIKE :q OR m.director LIKE :q2 OR m.cast LIKE :q3)";
    $params[':q']  = '%' . $searchQuery . '%';
    $params[':q2'] = '%' . $searchQuery . '%';
    $params[':q3'] = '%' . $searchQuery . '%';
}

if ($genreFilter !== '') {
    $where[] = "m.genre = :genre";
    $params[':genre'] = $genreFilter;
}

if ($langFilter !== '') {
    $where[] = "m.language = :lang";
    $params[':lang'] = $langFilter;
}

if (in_array($statusFilter, ['Now Showing', 'Coming Soon'], true)) {
    $where[] = "m.listing_status = :listing_status";
    $params[':listing_status'] = $statusFilter;
}

$orderBySql = match ($sortBy) {
    'title_asc'    => 'm.title ASC',
    'title_desc'   => 'm.title DESC',
    'release_asc'  => 'm.release_date ASC',
    'duration_desc'=> 'm.duration_minutes DESC',
    'duration_asc' => 'm.duration_minutes ASC',
    default        => 'm.listing_status ASC, m.release_date DESC',
};

$whereSql = implode(' AND ', $where);

// Count total matching movies
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM movies m WHERE {$whereSql}");
$countStmt->execute($params);
$totalMovies = (int)$countStmt->fetchColumn();
$totalPages  = max(1, (int)ceil($totalMovies / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;

// Fetch matching movies with future showtimes count
$sql = "SELECT m.*,
               (SELECT COUNT(*) FROM showtimes sh
                INNER JOIN screens sc ON sc.id = sh.screen_id
                INNER JOIN cinema_locations c ON c.id = sc.cinema_location_id
                WHERE sh.movie_id = m.id AND sh.status = 'Scheduled'
                  AND sh.start_datetime > :now1 AND sc.status = 'Active' AND c.status = 'Active') AS future_showtimes_count,
               (SELECT MIN(sh.base_ticket_price) FROM showtimes sh
                WHERE sh.movie_id = m.id AND sh.status = 'Scheduled' AND sh.start_datetime > :now2) AS min_price
        FROM movies m
        WHERE {$whereSql}
        ORDER BY {$orderBySql}
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':now1', $nowStr);
$stmt->bindValue(':now2', $nowStr);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movies = $stmt->fetchAll();

// Filter options from DB
$genresList = $pdo->query("SELECT DISTINCT genre FROM movies WHERE status = 'Active' ORDER BY genre ASC")->fetchAll();
$langsList  = $pdo->query("SELECT DISTINCT language FROM movies WHERE status = 'Active' ORDER BY language ASC")->fetchAll();

$pageTitle = 'Movies';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">Complete Catalogue</span>
        <h1 class="page-hero-title">Explore All Movies</h1>
        <p class="page-hero-subtitle">Search and filter active presentations across our Colombo, Kandy, and Galle multiplexes.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <form method="get" action="<?= e(url('movies.php')) ?>" class="filter-bar" aria-label="Filter movies">
            <div>
                <label for="f_q" class="form-label">Search Title / Cast / Director</label>
                <input type="text" id="f_q" name="q" class="form-control" value="<?= e($searchQuery) ?>" placeholder="Search movies...">
            </div>

            <div>
                <label for="f_genre" class="form-label">Genre</label>
                <select id="f_genre" name="genre" class="form-select">
                    <option value="">All Genres</option>
                    <?php foreach ($genresList as $g): ?>
                        <option value="<?= e($g['genre']) ?>" <?= $genreFilter === $g['genre'] ? 'selected' : '' ?>><?= e($g['genre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="f_lang" class="form-label">Language</label>
                <select id="f_lang" name="language" class="form-select">
                    <option value="">All Languages</option>
                    <?php foreach ($langsList as $l): ?>
                        <option value="<?= e($l['language']) ?>" <?= $langFilter === $l['language'] ? 'selected' : '' ?>><?= e($l['language']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="f_status" class="form-label">Status</label>
                <select id="f_status" name="listing_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Now Showing" <?= $statusFilter === 'Now Showing' ? 'selected' : '' ?>>Now Showing</option>
                    <option value="Coming Soon" <?= $statusFilter === 'Coming Soon' ? 'selected' : '' ?>>Coming Soon</option>
                </select>
            </div>

            <div>
                <label for="f_sort" class="form-label">Sort By</label>
                <select id="f_sort" name="sort" class="form-select">
                    <option value="release_desc" <?= $sortBy === 'release_desc' ? 'selected' : '' ?>>Release Date (Newest)</option>
                    <option value="release_asc" <?= $sortBy === 'release_asc' ? 'selected' : '' ?>>Release Date (Oldest)</option>
                    <option value="title_asc" <?= $sortBy === 'title_asc' ? 'selected' : '' ?>>Title (A–Z)</option>
                    <option value="title_desc" <?= $sortBy === 'title_desc' ? 'selected' : '' ?>>Title (Z–A)</option>
                    <option value="duration_desc" <?= $sortBy === 'duration_desc' ? 'selected' : '' ?>>Duration (Longest)</option>
                    <option value="duration_asc" <?= $sortBy === 'duration_asc' ? 'selected' : '' ?>>Duration (Shortest)</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Apply</button>
                <a href="<?= e(url('movies.php')) ?>" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <span class="text-muted" style="font-size: 0.9rem;">
                Showing <strong><?= count($movies) ?></strong> of <strong><?= $totalMovies ?></strong> active movies
            </span>
        </div>

        <?php if (empty($movies)): ?>
            <div class="empty-state">
                <h3>No Movies Match Your Selected Filters</h3>
                <p>Try clearing one or more search filters to view all active CINEMORA movies.</p>
                <a href="<?= e(url('movies.php')) ?>" class="btn btn-primary">Reset All Filters</a>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php
                        $isNowShowing = ($movie['listing_status'] === 'Now Showing');
                        $hasShows = ((int)($movie['future_showtimes_count'] ?? 0) > 0);
                    ?>
                    <article class="movie-card">
                        <a href="<?= e(url('movie-details.php?id=' . (int)$movie['id'])) ?>" class="movie-poster-wrap">
                            <img src="<?= e(resolve_poster_url($movie['poster'])) ?>" alt="<?= e($movie['title']) ?>" loading="lazy">
                            <div class="movie-card-badges">
                                <span class="pill-badge <?= $isNowShowing ? 'badge-green' : 'badge-gold' ?>">
                                    <?= e($movie['listing_status']) ?>
                                </span>
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
                            <div class="text-muted" style="font-size: 0.81rem; margin-bottom: 14px;">
                                Release: <?= e(format_date($movie['release_date'])) ?>
                                <?php if ($hasShows && !empty($movie['min_price'])): ?>
                                    <br><span class="text-gold" style="font-weight: 700;">From <?= e(format_lkr($movie['min_price'])) ?></span>
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

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Movies pagination">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php
                            $queryParams = $_GET;
                            $queryParams['page'] = $p;
                            $pageHref = url('movies.php?' . http_build_query($queryParams));
                        ?>
                        <a href="<?= e($pageHref) ?>" class="page-link <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
