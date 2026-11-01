<?php
declare(strict_types=1);

/**
 * CINEMORA — About Page (about.php)
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$cinemaCount = (int)$pdo->query("SELECT COUNT(*) FROM cinema_locations WHERE status = 'Active'")->fetchColumn();
$screenCount = (int)$pdo->query("SELECT COUNT(*) FROM screens WHERE status = 'Active'")->fetchColumn();
$seatCount   = (int)$pdo->query("SELECT COUNT(*) FROM seats WHERE is_active = 1")->fetchColumn();

$pageTitle = 'About CINEMORA';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">Our Story &amp; Architecture</span>
        <h1 class="page-hero-title">About CINEMORA</h1>
        <p class="page-hero-subtitle">“Experience Movies. Reserve Your Moment.” — Sri Lanka’s premier multi-cinema movie ticket booking destination.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px; margin-bottom: 40px;">
            <div class="glass-card">
                <span class="pill-badge badge-gold" style="margin-bottom: 12px;">Multi-Branch Multiplex Network</span>
                <h2 style="margin: 0 0 10px; font-size: 1.35rem;">Multiple Locations, Bespoke Halls</h2>
                <p class="text-muted" style="margin: 0;">
                    CINEMORA operates flagship cinema branches across <strong>Colombo</strong>, <strong>Kandy</strong>, and <strong>Galle</strong>. Every branch houses multiple distinct auditorium halls—from high-capacity 4K Laser auditoriums to intimate VIP Recliner suites.
                </p>
            </div>

            <div class="glass-card">
                <span class="pill-badge badge-red" style="margin-bottom: 12px;">Precision Seat Engineering</span>
                <h2 style="margin: 0 0 10px; font-size: 1.35rem;">Custom Auditorium Seat Maps</h2>
                <p class="text-muted" style="margin: 0;">
                    Unlike generic booking sites that use a single static grid, every CINEMORA hall has its own custom seat layout complete with real walkways, aisles, corner gaps, wheelchair-accessible spaces, and Standard, Premium, and VIP tiers.
                </p>
            </div>

            <div class="glass-card">
                <span class="pill-badge badge-green" style="margin-bottom: 12px;">Transparent LKR Reservations</span>
                <h2 style="margin: 0 0 10px; font-size: 1.35rem;">Instant Lock &amp; Pay at Cinema</h2>
                <p class="text-muted" style="margin: 0;">
                    Our transactional booking engine locks your exact seats in real time so two guests can never double-book the same seat. Confirm your booking online and settle your ticket total in <strong>Sri Lankan Rupees (LKR)</strong> at the cinema box office.
                </p>
            </div>
        </div>

        <div class="glass-card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; text-align: center; border-color: var(--border-gold);">
            <div>
                <div style="font-size: 2.2rem; font-weight: 900; color: var(--accent-gold);"><?= $cinemaCount ?></div>
                <div class="text-muted">Active Cinema Branches</div>
            </div>
            <div>
                <div style="font-size: 2.2rem; font-weight: 900; color: var(--accent-primary);"><?= $screenCount ?></div>
                <div class="text-muted">Custom-Designed Auditoriums</div>
            </div>
            <div>
                <div style="font-size: 2.2rem; font-weight: 900; color: var(--color-success);"><?= $seatCount ?></div>
                <div class="text-muted">Configured Luxury Seats</div>
            </div>
            <div>
                <div style="font-size: 2.2rem; font-weight: 900; color: #FFFFFF;">LKR</div>
                <div class="text-muted">Transparent Local Pricing</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
