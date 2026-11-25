<?= $this->extend('auth_layout') ?>

<?= $this->section('content') ?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Reset Password</h1>
        <p>Enter the 6-digit code from your email and choose a new password.</p>
    </div>

    <div class="auth-body">
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">⚠️</span>
                <span><?= esc(session('error')) ?></span>
            </div>
        <?php endif; ?>
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <span><?= esc(session('success')) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('auth/reset') ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= esc(old('email') ?? $email) ?>" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="code" class="form-label">6-Digit Code</label>
                <input type="text" class="form-control" id="code" name="code"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="123456"
                       autocomplete="one-time-code" required autofocus
                       style="letter-spacing: 6px; font-size: 1.25rem; font-weight: 700;">
            </div>

            <div class="form-group">
                <label for="new_password" class="form-label">New Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="new_password" name="new_password"
                           minlength="8" placeholder="At least 8 characters" autocomplete="new-password" required>
                    <button type="button" class="password-toggle" data-target="new_password" title="Show password">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           minlength="8" placeholder="Type the new password again" autocomplete="new-password" required>
                    <button type="button" class="password-toggle" data-target="confirm_password" title="Show password">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>

        <p style="text-align: center; font-size: 0.9rem; margin: 8px 0 0;">
            Didn't get a code? <a href="<?= base_url('auth/forgot') ?>" style="color: var(--safety-orange); text-decoration: none; font-weight: 600;">Send a new one</a>
        </p>
        <p style="text-align: center; font-size: 0.85rem; margin: 14px 0 0;">
            <a href="<?= base_url('auth') ?>" style="color: #666; text-decoration: none; font-weight: 600;">← Back to Sign In</a>
        </p>
    </div>
</div>

<script>
document.getElementById('code').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
document.querySelectorAll('.password-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? '🙈' : '👁️';
        btn.title = show ? 'Hide password' : 'Show password';
    });
});
</script>

<?= $this->endSection() ?>
