<?= $this->extend('auth_layout') ?>

<?= $this->section('content') ?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Sign in to your business's Gmack dashboard</p>
    </div>

    <div class="auth-body">
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">⚠️</span>
                <span><?= session('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->has('success')): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <span><?= session('success') ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('auth/login') ?>" id="loginForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username" class="form-label">Username or Email</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your username or email"
                    autocomplete="username"
                    required
                >
                <span class="form-error" id="usernameError"></span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="password-toggle" id="passwordToggle" title="Show password">
                        👁️
                    </button>
                </div>
                <span class="form-error" id="passwordError"></span>
            </div>

            <div class="checkbox-group" style="justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <span style="display: flex; align-items: center;">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me for 30 days</label>
                </span>
                <a href="<?= base_url('auth/forgot') ?>" style="color: var(--safety-orange); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary" id="loginBtn">
                Sign In to Your Account
            </button>
        </form>

        <div class="divider">
            <span>Don't have an account?</span>
        </div>

        <a href="<?= base_url('auth/register') ?>" class="btn btn-secondary">
            Create New Account
        </a>

        <div class="divider">
            <span>Or</span>
        </div>

        <p style="text-align: center; color: #666; font-size: 0.85rem; margin-bottom: 0;">
            <a href="<?= base_url() ?>" style="color: var(--safety-orange); text-decoration: none; font-weight: 600;">
                ← Back to Home
            </a>
        </p>
    </div>
</div>

<script>
    // Password visibility toggle
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');

    passwordToggle.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggle.textContent = isPassword ? '🙈' : '👁️';
    });

    // Form submission handling
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    loginForm.addEventListener('submit', (e) => {
        // Simple validation
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        let isValid = true;

        // Clear previous errors
        document.getElementById('usernameError').style.display = 'none';
        document.getElementById('passwordError').style.display = 'none';
        document.getElementById('username').classList.remove('error');
        document.getElementById('password').classList.remove('error');

        if (!username) {
            document.getElementById('usernameError').textContent = 'Username is required';
            document.getElementById('usernameError').style.display = 'block';
            document.getElementById('username').classList.add('error');
            isValid = false;
        }

        if (!password) {
            document.getElementById('passwordError').textContent = 'Password is required';
            document.getElementById('passwordError').style.display = 'block';
            document.getElementById('password').classList.add('error');
            isValid = false;
        }

        if (isValid) {
            // Show loading state
            loginBtn.classList.add('loading');
            loginBtn.innerHTML = '<span class="spinner"></span>Signing in...';
        } else {
            e.preventDefault();
        }
    });

    // Clear error messages on input
    document.getElementById('username').addEventListener('input', () => {
        document.getElementById('usernameError').style.display = 'none';
        document.getElementById('username').classList.remove('error');
    });

    document.getElementById('password').addEventListener('input', () => {
        document.getElementById('passwordError').style.display = 'none';
        document.getElementById('password').classList.remove('error');
    });

    // Set focus on first input
    window.addEventListener('load', () => {
        document.getElementById('username').focus();
    });
</script>

<?= $this->endSection() ?>
