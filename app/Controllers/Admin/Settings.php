<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class Settings extends BaseController
{
    protected $settingModel;
    protected $businessModel;

    /**
     * Synthesized alarm tones the cashier POS can play for an expired or
     * low-stock item. Each key must match a JS player function
     * (playAlarm<PascalCase key without underscores>) in cashier/sales.php.
     */
    public const ALARM_SOUNDS = [
        'classic' => 'Classic Beep',
        'siren'   => 'Siren',
        'pulse'   => 'Triple Pulse',
        'chime'   => 'Chime',
        'buzzer'  => 'Alarm Clock Buzzer',
        'custom'  => 'Custom (uploaded file)',
    ];

    protected const CUSTOM_ALARM_MAX_MB = 2;
    protected const CUSTOM_ALARM_EXTENSIONS = ['mp3', 'wav', 'ogg', 'm4a'];

    public function __construct()
    {
        $this->settingModel = new SettingModel();
        $this->businessModel = new BusinessModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();

        $data['business'] = $this->businessModel->find($businessId);
        $data['or_prefix'] = $this->settingModel->getSetting($businessId, 'or_prefix', 'OR-');
        $data['or_next_number'] = $this->settingModel->getSetting($businessId, 'or_next_number', '1');
        $data['alarm_sound'] = $this->settingModel->getSetting($businessId, 'alarm_sound', 'classic');
        $data['alarm_sounds'] = self::ALARM_SOUNDS;
        $data['alarm_custom_url'] = $this->customAlarmUrl($businessId);
        $data['account'] = (new UserModel())->find(session()->get('user_id'));
        $data['impersonating'] = (bool) session()->get('impersonating');

        return view('admin/settings/index', $data);
    }

    /**
     * Lets the signed-in admin change their own email and/or password.
     * The current password is always required, so an unattended logged-in
     * screen can't be used to take over the account.
     */
    public function updateAccount()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        // A superadmin switched into a store is signed in under their own
        // user id; this form must not change the superadmin's credentials.
        if (session()->get('impersonating')) {
            session()->setFlashdata('error', 'You are viewing this store as the superadmin. Account details can only be changed by the store admin.');
            return redirect()->to('/admin/settings');
        }

        $userModel = new UserModel();
        $userId = (int) session()->get('user_id');
        $user = $userModel->find($userId);
        if (!$user) {
            return redirect()->to('/auth');
        }

        $current = (string) $this->request->getPost('current_password');
        $email = trim((string) $this->request->getPost('email'));
        $newPassword = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');

        if ($current === '' || !password_verify($current, $user['password'])) {
            session()->setFlashdata('error', 'Your current password is incorrect. Nothing was changed.');
            return redirect()->to('/admin/settings#account');
        }

        $update = [];
        $changed = [];

        if ($email !== $user['email']) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                session()->setFlashdata('error', 'Please enter a valid email address.');
                return redirect()->to('/admin/settings#account');
            }
            if ($userModel->where('email', $email)->where('id !=', $userId)->first()) {
                session()->setFlashdata('error', 'That email is already used by another account.');
                return redirect()->to('/admin/settings#account');
            }
            $update['email'] = $email;
            $changed[] = 'email';
        }

        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                session()->setFlashdata('error', 'Your new password must be at least 8 characters.');
                return redirect()->to('/admin/settings#account');
            }
            if ($newPassword !== $confirm) {
                session()->setFlashdata('error', 'The new password and confirmation do not match.');
                return redirect()->to('/admin/settings#account');
            }
            if (password_verify($newPassword, $user['password'])) {
                session()->setFlashdata('error', 'Your new password must be different from your current one.');
                return redirect()->to('/admin/settings#account');
            }
            $update['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $changed[] = 'password';
        }

        if (!$update) {
            session()->setFlashdata('error', 'No changes to save. Enter a new email or a new password.');
            return redirect()->to('/admin/settings#account');
        }

        $update['updated_at'] = date('Y-m-d H:i:s');
        $userModel->update($userId, $update);

        if (isset($update['password'])) {
            // New session id after a credential change.
            session()->regenerate(true);
        }

        $this->logAudit('account.updated', 'User "' . $user['username'] . '" changed their ' . implode(' and ', $changed));
        session()->setFlashdata('success', 'Your ' . implode(' and ', $changed) . ' ' . (count($changed) > 1 ? 'were' : 'was') . ' updated.');
        return redirect()->to('/admin/settings#account');
    }

    public function save()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to('/auth');
        }

        $businessId = $this->currentBusinessId();

        $orPrefix = $this->request->getPost('or_prefix');
        $orNextNumber = (int) $this->request->getPost('or_next_number');

        if ($orNextNumber < 1) {
            $orNextNumber = 1;
        }

        $this->settingModel->setSetting($businessId, 'or_prefix', $orPrefix ?? 'OR-');
        $this->settingModel->setSetting($businessId, 'or_next_number', (string) $orNextNumber);

        $alarmSound = $this->request->getPost('alarm_sound');
        if (!array_key_exists($alarmSound, self::ALARM_SOUNDS)) {
            $alarmSound = 'classic';
        }

        $uploadError = null;
        if ($this->request->getPost('remove_custom_alarm')) {
            $this->deleteCustomAlarm($businessId);
            if ($alarmSound === 'custom') {
                $alarmSound = 'classic';
            }
        }

        $file = $this->request->getFile('custom_alarm');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$file->isValid()) {
                $uploadError = 'Alarm file could not be uploaded (' . $file->getErrorString() . '). Max ' . self::CUSTOM_ALARM_MAX_MB . ' MB.';
            } else {
                $uploadError = $this->storeCustomAlarm($businessId, $file);
                if ($uploadError === null) {
                    $alarmSound = 'custom';
                }
            }
        }

        if ($alarmSound === 'custom' && !$this->customAlarmUrl($businessId)) {
            $alarmSound = 'classic';
        }
        $this->settingModel->setSetting($businessId, 'alarm_sound', $alarmSound);

        $businessName = trim((string) $this->request->getPost('business_name'));
        if ($businessName !== '') {
            $this->businessModel->update($businessId, [
                'name'    => $businessName,
                'address' => trim((string) $this->request->getPost('address')),
                'phone'   => trim((string) $this->request->getPost('phone')),
            ]);
            session()->set('business_name', $businessName);
        }

        $this->logAudit('settings.updated', 'Updated business/receipt settings');

        if ($uploadError !== null) {
            session()->setFlashdata('error', $uploadError);
        } else {
            session()->setFlashdata('success', 'Settings saved successfully. Next OR number: ' . $orPrefix . $orNextNumber);
        }
        return redirect()->to('/admin/settings');
    }

    /** Public URL of this business's uploaded alarm file, or null if none. */
    protected function customAlarmUrl(int $businessId): ?string
    {
        $path = $this->settingModel->getSetting($businessId, 'alarm_custom_path', '');
        if ($path === '' || !is_file(FCPATH . $path)) {
            return null;
        }
        return base_url($path);
    }

    /** Validates and stores the uploaded alarm file. Returns an error message, or null on success. */
    protected function storeCustomAlarm(int $businessId, $file): ?string
    {
        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, self::CUSTOM_ALARM_EXTENSIONS, true)) {
            return 'Alarm file must be one of: ' . implode(', ', self::CUSTOM_ALARM_EXTENSIONS) . '.';
        }
        if ($file->getSize() > self::CUSTOM_ALARM_MAX_MB * 1024 * 1024) {
            return 'Alarm file must be ' . self::CUSTOM_ALARM_MAX_MB . ' MB or smaller.';
        }
        if (strpos((string) $file->getMimeType(), 'audio/') !== 0 && $file->getMimeType() !== 'video/mp4') {
            return 'The uploaded file is not an audio file.';
        }

        $this->deleteCustomAlarm($businessId);

        $dir = 'uploads/alarms/' . $businessId;
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move(FCPATH . $dir, $newName);

        $this->settingModel->setSetting($businessId, 'alarm_custom_path', $dir . '/' . $newName);
        return null;
    }

    protected function deleteCustomAlarm(int $businessId): void
    {
        $path = $this->settingModel->getSetting($businessId, 'alarm_custom_path', '');
        if ($path !== '' && is_file(FCPATH . $path)) {
            unlink(FCPATH . $path);
        }
        $this->settingModel->setSetting($businessId, 'alarm_custom_path', '');
    }
}
