<?= $this->extend('auth_layout') ?>

<?= $this->section('content') ?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Register Your Business</h1>
        <p>Get your own Gmack instance in a minute</p>
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

        <form method="post" action="<?= base_url('auth/register') ?>" id="registerForm">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="business_name" class="form-label">Business / Company Name</label>
                <input
                    type="text"
                    class="form-control"
                    id="business_name"
                    name="business_name"
                    placeholder="e.g. Ace Hardware &amp; Supply"
                    value="<?= old('business_name') ?>"
                    minlength="2"
                    required
                >
                <small style="color: #999; display: block; margin-top: 5px;">This becomes your own isolated Gmack workspace</small>
                <span class="form-error" id="businessNameError"></span>
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Your Username</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="Choose your username"
                    autocomplete="username"
                    value="<?= old('username') ?>"
                    minlength="3"
                    required
                >
                <small style="color: #999; display: block; margin-top: 5px;">Minimum 3 characters</small>
                <span class="form-error" id="usernameError"></span>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    placeholder="your@email.com"
                    autocomplete="email"
                    value="<?= old('email') ?>"
                    required
                >
                <span class="form-error" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Create a strong password"
                        autocomplete="new-password"
                        minlength="6"
                        required
                    >
                    <button type="button" class="password-toggle" id="passwordToggle" title="Show password">
                        👁️
                    </button>
                </div>
                <small style="color: #999; display: block; margin-top: 5px;">Minimum 6 characters</small>
                <span class="form-error" id="passwordError"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="password-wrapper">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="password-toggle" id="confirmPasswordToggle" title="Show password">
                        👁️
                    </button>
                </div>
                <span class="form-error" id="confirmPasswordError"></span>
            </div>

            <button type="submit" class="btn btn-primary" id="registerBtn">
                Create Business &amp; Account
            </button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <a href="<?= base_url('auth') ?>" class="btn btn-secondary">
            Sign In Instead
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

    <div class="auth-footer">
        <small>
            You'll be the <strong>admin</strong> of your own isolated business workspace —<br>
            add cashiers and manage everything from your dashboard.
        </small>
    </div>
</div>

<script>
    // Password visibility toggle for password field
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');

    passwordToggle.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggle.textContent = isPassword ? '🙈' : '👁️';
    });

    // Password visibility toggle for confirm password field
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
    const confirmPasswordInput = document.getElementById('confirm_password');

    confirmPasswordToggle.addEventListener('click', () => {
        const isPassword = confirmPasswordInput.type === 'password';
        confirmPasswordInput.type = isPassword ? 'text' : 'password';
        confirmPasswordToggle.textContent = isPassword ? '🙈' : '👁️';
    });

    // Form validation
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');

    registerForm.addEventListener('submit', (e) => {
        const businessName = document.getElementById('business_name').value.trim();
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.form-error').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));

        // Business name validation
        if (businessName.length < 2) {
            document.getElementById('businessNameError').textContent = 'Please enter your business/company name';
            document.getElementById('businessNameError').style.display = 'block';
            document.getElementById('business_name').classList.add('error');
            isValid = false;
        }

        // Username validation
        if (!username) {
            document.getElementById('usernameError').textContent = 'Username is required';
            document.getElementById('usernameError').style.display = 'block';
            document.getElementById('username').classList.add('error');
            isValid = false;
        } else if (username.length < 3) {
            document.getElementById('usernameError').textContent = 'Username must be at least 3 characters';
            document.getElementById('usernameError').style.display = 'block';
            document.getElementById('username').classList.add('error');
            isValid = false;
        }

        // Email validation
        if (!email) {
            document.getElementById('emailError').textContent = 'Email is required';
            document.getElementById('emailError').style.display = 'block';
            document.getElementById('email').classList.add('error');
            isValid = false;
        } else if (!email.includes('@')) {
            document.getElementById('emailError').textContent = 'Please enter a valid email';
            document.getElementById('emailError').style.display = 'block';
            document.getElementById('email').classList.add('error');
            isValid = false;
        }

        // Password validation
        if (!password) {
            document.getElementById('passwordError').textContent = 'Password is required';
            document.getElementById('passwordError').style.display = 'block';
            document.getElementById('password').classList.add('error');
            isValid = false;
        } else if (password.length < 6) {
            document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
            document.getElementById('passwordError').style.display = 'block';
            document.getElementById('password').classList.add('error');
            isValid = false;
        }

        // Confirm password validation
        if (!confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Please confirm your password';
            document.getElementById('confirmPasswordError').style.display = 'block';
            document.getElementById('confirm_password').classList.add('error');
            isValid = false;
        } else if (password !== confirmPassword) {
            document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
            document.getElementById('confirmPasswordError').style.display = 'block';
            document.getElementById('confirm_password').classList.add('error');
            isValid = false;
        }

        if (isValid) {
            // Show loading state
            registerBtn.classList.add('loading');
            registerBtn.innerHTML = '<span class="spinner"></span>Creating account...';
        } else {
            e.preventDefault();
        }
    });

    // Clear error messages on input
    document.getElementById('business_name').addEventListener('input', () => {
        document.getElementById('businessNameError').style.display = 'none';
        document.getElementById('business_name').classList.remove('error');
    });

    document.getElementById('username').addEventListener('input', () => {
        document.getElementById('usernameError').style.display = 'none';
        document.getElementById('username').classList.remove('error');
    });

    document.getElementById('email').addEventListener('input', () => {
        document.getElementById('emailError').style.display = 'none';
        document.getElementById('email').classList.remove('error');
    });

    document.getElementById('password').addEventListener('input', () => {
        document.getElementById('passwordError').style.display = 'none';
        document.getElementById('password').classList.remove('error');
    });

    document.getElementById('confirm_password').addEventListener('input', () => {
        document.getElementById('confirmPasswordError').style.display = 'none';
        document.getElementById('confirm_password').classList.remove('error');
    });

    // Set focus on first input
    window.addEventListener('load', () => {
        document.getElementById('business_name').focus();
    });
</script>

<?= $this->endSection() ?>
