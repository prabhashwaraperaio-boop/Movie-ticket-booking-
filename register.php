<?php
declare(strict_types=1);

/**
 * CINEMORA — Customer Registration Page (register.php)
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'customer/dashboard.php');
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section" style="padding: 56px 0;">
    <div class="container" style="max-width: 580px;">
        <div class="glass-card" style="padding: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 22px;">
                <div>
                    <span class="section-eyebrow">Customer Membership</span>
                    <h1 style="margin: 4px 0 6px; font-size: 1.75rem;">Create Your Account</h1>
                    <p class="text-muted" style="margin: 0; font-size: 0.9rem;">Reserve theater seats, view booking history, and print digital tickets.</p>
                </div>
                <button type="button" class="btn btn-demo" data-fill-demo="register" data-target-form="registerForm">
                    ✦ Fill Demo Details
                </button>
            </div>

            <form id="registerForm" method="post" action="<?= e(url('actions/auth-actions.php')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label for="reg_full_name" class="form-label">Full Name <span class="req">*</span></label>
                    <input type="text" id="reg_full_name" name="full_name" class="form-control" required maxlength="120" value="<?= old('full_name') ?>" placeholder="e.g. Kasun Perera">
                    <?= render_field_error('full_name') ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg_email" class="form-label">Email Address <span class="req">*</span></label>
                        <input type="email" id="reg_email" name="email" class="form-control" required maxlength="190" value="<?= old('email') ?>" placeholder="you@example.com">
                        <?= render_field_error('email') ?>
                    </div>

                    <div class="form-group">
                        <label for="reg_phone" class="form-label">Phone Number <span class="req">*</span></label>
                        <input type="tel" id="reg_phone" name="phone" class="form-control" required maxlength="30" value="<?= old('phone') ?>" placeholder="+94 77 123 4567">
                        <?= render_field_error('phone') ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg_password" class="form-label">Password <span class="req">*</span></label>
                        <input type="password" id="reg_password" name="password" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Min 8 chars, upper, lower &amp; number">
                        <?= render_field_error('password') ?>
                    </div>

                    <div class="form-group">
                        <label for="reg_password_confirm" class="form-label">Confirm Password <span class="req">*</span></label>
                        <input type="password" id="reg_password_confirm" name="password_confirm" class="form-control" required minlength="8" autocomplete="new-password" placeholder="Re-enter password">
                        <?= render_field_error('password_confirm') ?>
                    </div>
                </div>

                <div class="form-help" style="margin-bottom: 20px;">
                    Passwords must be at least 8 characters long and include uppercase, lowercase, and numeric characters.
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Create Customer Account</button>
            </form>

            <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-subtle); text-align: center; font-size: 0.9rem;" class="text-muted">
                Already have a CINEMORA account?
                <a href="<?= e(url('login.php')) ?>" class="text-gold" style="font-weight: 700;">Sign In Here</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
