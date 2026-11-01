<?php
declare(strict_types=1);

/**
 * CINEMORA — Contact Concierge Page (contact.php)
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db();
$user = current_user();
$supportEmail = get_setting($pdo, 'support_email', 'concierge@cinemora.local');
$supportPhone = get_setting($pdo, 'support_phone', '+94 11 234 5678');

$cinemas = $pdo->query("SELECT cinema_name, city, address, phone, email FROM cinema_locations WHERE status = 'Active' ORDER BY id ASC")->fetchAll();

$defaultName  = old('name', $user['full_name'] ?? '');
$defaultEmail = old('email', $user['email'] ?? '');
$defaultPhone = old('phone', $user['phone'] ?? '');

$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <span class="section-eyebrow">Guest Support &amp; Private Screenings</span>
        <h1 class="page-hero-title">Contact CINEMORA Concierge</h1>
        <p class="page-hero-subtitle">Send us a message regarding ticket reservations, group bookings, accessibility assistance, or private auditorium hire.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 36px; align-items: start;" class="hero-grid">
            <div class="glass-card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 22px;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.35rem;">Send a Message</h2>
                        <p class="text-muted" style="margin: 4px 0 0; font-size: 0.88rem;">Fields marked with <span class="text-crimson">*</span> are required.</p>
                    </div>
                    <button type="button" class="btn btn-demo" data-fill-demo="contact" data-target-form="contactForm">
                        ✦ Fill Demo Details
                    </button>
                </div>

                <form id="contactForm" method="post" action="<?= e(url('actions/contact-actions.php')) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_name" class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" id="contact_name" name="name" class="form-control" required maxlength="120" value="<?= $defaultName ?>" placeholder="e.g. Kasun Perera">
                            <?= render_field_error('name') ?>
                        </div>

                        <div class="form-group">
                            <label for="contact_email" class="form-label">Email Address <span class="req">*</span></label>
                            <input type="email" id="contact_email" name="email" class="form-control" required maxlength="190" value="<?= $defaultEmail ?>" placeholder="e.g. kasun@example.com">
                            <?= render_field_error('email') ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_phone" class="form-label">Phone Number (Optional)</label>
                            <input type="tel" id="contact_phone" name="phone" class="form-control" maxlength="30" value="<?= $defaultPhone ?>" placeholder="e.g. +94 77 412 8901">
                            <?= render_field_error('phone') ?>
                        </div>

                        <div class="form-group">
                            <label for="contact_subject" class="form-label">Subject <span class="req">*</span></label>
                            <input type="text" id="contact_subject" name="subject" class="form-control" required maxlength="180" value="<?= old('subject') ?>" placeholder="e.g. Group Booking Inquiry">
                            <?= render_field_error('subject') ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_message" class="form-label">Message <span class="req">*</span></label>
                        <textarea id="contact_message" name="message" class="form-textarea" required rows="5" placeholder="How can our box office team assist you?"><?= old('message') ?></textarea>
                        <?= render_field_error('message') ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Submit Message</button>
                </form>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="glass-card">
                    <h3 style="margin: 0 0 12px; font-size: 1.15rem;">Central Box Office Desk</h3>
                    <div class="text-muted" style="font-size: 0.9rem; line-height: 1.8;">
                        <div><strong>Telephone:</strong> <?= e($supportPhone) ?></div>
                        <div><strong>Email:</strong> <?= e($supportEmail) ?></div>
                        <div><strong>Hours:</strong> Daily 09:00 AM – 10:30 PM (Asia/Colombo)</div>
                    </div>
                </div>

                <?php foreach ($cinemas as $c): ?>
                    <div class="info-box">
                        <strong style="color: var(--accent-gold);"><?= e($c['cinema_name']) ?> (<?= e($c['city']) ?>)</strong>
                        <div class="text-muted" style="font-size: 0.85rem; margin-top: 4px;">
                            <?= e($c['address']) ?><br>
                            Tel: <?= e($c['phone']) ?>
                            <?php if (!empty($c['email'])): ?> • <?= e($c['email']) ?><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
