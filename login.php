<?php
declare(strict_types=1);

/**
 * CINEMORA — Customer & Administrator Login Page (login.php)
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'customer/dashboard.php');
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section" style="padding: 56px 0;">
    <div class="container" style="max-width: 540px;">
        <div class="glass-card" style="padding: 36px;">
            <span class="section-eyebrow">Member &amp; Staff Access</span>
            <h1 style="margin: 4px 0 8px; font-size: 1.8rem;">Sign In to CINEMORA</h1>
            <p class="text-muted" style="margin: 0 0 24px; font-size: 0.92rem;">
                Enter your registered email and password to manage bookings or access the administrator portal.
            </p>

            <form id="loginForm" method="post" action="<?= e(url('actions/auth-actions.php')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="login_email" class="form-label">Email Address <span class="req">*</span></label>
                    <input type="email" id="login_email" name="email" class="form-control" required maxlength="190" value="<?= old('email') ?>" autocomplete="username" placeholder="you@cinemora.local">
                    <?= render_field_error('email') ?>
                </div>

                <div class="form-group">
                    <label for="login_password" class="form-label">Password <span class="req">*</span></label>
                    <input type="password" id="login_password" name="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password">
                    <?= render_field_error('password') ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 8px;">Sign In</button>
            </form>

            <!-- Local Demo Account Quick-Fill Box -->
            <div class="info-box" style="margin-top: 26px;">
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                    Local Evaluation Demo Credentials (Click to Fill)
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="button" class="btn btn-demo btn-sm"
                            data-fill-demo="login_admin"
                            data-target-form="loginForm"
                            data-demo-values='{"email":"admin@cinemora.local","password":"Admin@12345"}'>
                        Fill Admin Account
                    </button>
                    <button type="button" class="btn btn-demo btn-sm"
                            data-fill-demo="login_c1"
                            data-target-form="loginForm"
                            data-demo-values='{"email":"customer@cinemora.local","password":"Customer@12345"}'>
                        Fill Customer 1
                    </button>
                    <button type="button" class="btn btn-demo btn-sm"
                            data-fill-demo="login_c2"
                            data-target-form="loginForm"
                            data-demo-values='{"email":"customer2@cinemora.local","password":"Customer@12345"}'>
                        Fill Customer 2
                    </button>
                </div>
            </div>

            <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-subtle); text-align: center; font-size: 0.9rem;" class="text-muted">
                New to CINEMORA?
                <a href="<?= e(url('register.php')) ?>" class="text-gold" style="font-weight: 700;">Create a Customer Account</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
