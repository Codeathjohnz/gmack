<?= $this->extend('auth_layout') ?>

<?= $this->section('content') ?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Forgot Password</h1>
        <p>Enter the email on your account and we'll send you a 6-digit code.</p>
    </div>

    <div class="auth-body">
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">⚠️</span>
                <span><?= esc(session('error')) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('auth/forgot') ?>" id="forgotForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= esc(old('email') ?? '') ?>"
                       placeholder="you@example.com" autocomplete="email" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary" id="sendBtn">Send Code</button>
        </form>

        <p style="text-align: center; font-size: 0.9rem; margin: 8px 0 0;">
            <a href="<?= base_url('auth/reset') ?>" style="color: var(--safety-orange); text-decoration: none; font-weight: 600;">I already have a code</a>
        </p>
        <p style="text-align: center; font-size: 0.85rem; margin: 14px 0 0;">
            <a href="<?= base_url('auth') ?>" style="color: #666; text-decoration: none; font-weight: 600;">← Back to Sign In</a>
        </p>
    </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', function () {
    var btn = document.getElementById('sendBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<span class="spinner"></span>Sending...';
});
</script>

<?= $this->endSection() ?>
