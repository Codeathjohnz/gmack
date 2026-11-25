<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <h5 class="fw-bold mb-3"><i class="bi bi-gear"></i> Settings</h5>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('admin/settings/save') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-buildings"></i> Business Profile
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">This is what shows up as your business name across the dashboard, receipts, and reports.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="business_name" class="form-label fw-bold">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name"
                               value="<?= esc($business['name'] ?? '') ?>" required minlength="2">
                    </div>
                    <div class="col-md-3">
                        <label for="phone" class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?= esc($business['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                               value="<?= esc($business['address'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-receipt"></i> OR (Official Receipt) Number
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Set the prefix and starting number for receipt OR numbers. Once a sale is completed, the number auto-increments.
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="or_prefix" class="form-label fw-bold">OR Prefix</label>
                        <input type="text" class="form-control" id="or_prefix" name="or_prefix"
                               value="<?= esc($or_prefix) ?>" placeholder="e.g. OR-" maxlength="20">
                        <small class="text-muted">Text before the number (e.g. OR-, REC-, #)</small>
                    </div>
                    <div class="col-md-4">
                        <label for="or_next_number" class="form-label fw-bold">Next OR Number</label>
                        <input type="number" class="form-control" id="or_next_number" name="or_next_number"
                               value="<?= esc($or_next_number) ?>" min="1" step="1">
                        <small class="text-muted">The next sale will use this number</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Preview</label>
                        <div class="form-control bg-light" id="orPreview" style="font-size: 18px; font-weight: bold; letter-spacing: 1px;">
                            <?= esc($or_prefix . $or_next_number) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-bell"></i> Alarm Sound
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    The sound the cashier POS plays on repeat while any item is expired or at/below its low-stock threshold — until it's dismissed with the mute button.
                </p>
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="alarm_sound" class="form-label fw-bold">Sound</label>
                        <select class="form-select" id="alarm_sound" name="alarm_sound">
                            <?php foreach ($alarm_sounds as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= ($alarm_sound ?? 'classic') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-secondary text-white w-100" id="previewAlarmBtn">
                            <i class="bi bi-play-circle"></i> Preview
                        </button>
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-1">
                    <div class="col-md-6">
                        <label for="custom_alarm" class="form-label fw-bold">Upload your own ringtone</label>
                        <input type="file" class="form-control" id="custom_alarm" name="custom_alarm" accept=".mp3,.wav,.ogg,.m4a,audio/*">
                        <small class="text-muted">MP3, WAV, OGG or M4A, up to 2 MB. A short beep (1–3 seconds) works best, since it repeats while the alarm is active. Uploading switches the sound to "Custom".</small>
                    </div>
                    <?php if (!empty($alarm_custom_url)): ?>
                    <div class="col-md-6">
                        <div class="small mb-2">Current file: <a href="<?= esc($alarm_custom_url) ?>" target="_blank" rel="noopener">listen</a></div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remove_custom_alarm" name="remove_custom_alarm" value="1">
                            <label class="form-check-label" for="remove_custom_alarm">Remove uploaded file</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
    </form>

    <form method="post" action="<?= site_url('admin/settings/account') ?>" id="account" class="mt-4" autocomplete="off">
        <?= csrf_field() ?>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-lock"></i> My Account
            </div>
            <div class="card-body">
                <?php if (!empty($impersonating)): ?>
                    <p class="text-muted mb-0">You are viewing this store as the superadmin. Only the store's own admin can change these account details.</p>
                <?php else: ?>
                    <p class="text-muted small mb-3">Change the email and password you use to sign in as <strong><?= esc($account['username'] ?? '') ?></strong>. Leave the new password blank to keep your current one.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="account_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="account_email" name="email" value="<?= esc($account['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" placeholder="Type the new password again">
                        </div>
                        <div class="col-md-6">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required placeholder="Required to save any change">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-shield-check"></i> Update Account</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('or_prefix').addEventListener('input', updatePreview);
document.getElementById('or_next_number').addEventListener('input', updatePreview);
function updatePreview() {
    const prefix = document.getElementById('or_prefix').value;
    const num = document.getElementById('or_next_number').value;
    document.getElementById('orPreview').textContent = prefix + num;
}

// ---- Alarm sound preview (same synthesized tones the cashier POS uses) ----
let previewAudioCtx = null;
const SAVED_CUSTOM_URL = <?= json_encode($alarm_custom_url ?? null) ?>;
function playAlarmPreview(kind) {
    // A file picked but not yet saved can't be previewed from the server, so
    // preview it locally from the file input.
    const picked = document.getElementById('custom_alarm').files[0];
    if (kind === 'custom' && picked) {
        const url = URL.createObjectURL(picked);
        playAlarmCustom(url);
        return;
    }
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return;
    if (!previewAudioCtx) previewAudioCtx = new Ctx();
    if (previewAudioCtx.state === 'suspended') previewAudioCtx.resume();
    playAlarmChoice(previewAudioCtx, kind, SAVED_CUSTOM_URL);
}
document.getElementById('previewAlarmBtn').addEventListener('click', function () {
    playAlarmPreview(document.getElementById('alarm_sound').value);
});
document.getElementById('custom_alarm').addEventListener('change', function (e) {
    const f = e.target.files[0];
    if (!f) return;
    if (f.size > 2 * 1024 * 1024) {
        alert('That file is over 2 MB. Please choose a smaller one.');
        e.target.value = '';
        return;
    }
    document.getElementById('alarm_sound').value = 'custom';
});
</script>

<?= view('components/alarm_tones') ?>

<?= $this->endSection() ?>
