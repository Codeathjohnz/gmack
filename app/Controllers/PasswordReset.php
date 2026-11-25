<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * "Forgot password" flow:
 *   1. /auth/forgot  - user enters their email; a 6-digit code is emailed.
 *   2. /auth/reset   - user enters the code and a new password.
 *
 * Codes expire after 10 minutes, allow 5 wrong tries, and only a hash of the
 * code is stored. The forgot page gives the same answer whether or not the
 * email exists, so it can't be used to discover which emails have accounts.
 */
class PasswordReset extends BaseController
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function forgot()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return view('auth/forgot');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('error', 'Please enter a valid email address.');
            return redirect()->back()->withInput();
        }

        $genericMessage = 'If an account uses that email, we sent a 6-digit code to it. Enter it below. The code expires in ' . self::CODE_TTL_MINUTES . ' minutes.';
        session()->set('reset_email', $email);

        $user = (new UserModel())->where('email', $email)->where('status', 'active')->first();
        if (!$user) {
            session()->setFlashdata('success', $genericMessage);
            return redirect()->to('/auth/reset');
        }

        $db = db_connect();
        $resets = $db->table('password_resets');

        // Don't send a new code more than once a minute per account.
        $recent = $resets->where('user_id', $user['id'])
            ->where('created_at >', date('Y-m-d H:i:s', time() - self::RESEND_COOLDOWN_SECONDS))
            ->countAllResults();
        if ($recent > 0) {
            session()->setFlashdata('success', 'A code was sent less than a minute ago. Check your inbox and spam folder, or wait a minute to request a new one.');
            return redirect()->to('/auth/reset');
        }

        // Only the newest code is valid.
        $db->table('password_resets')->where('user_id', $user['id'])->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        $code = (string) random_int(100000, 999999);
        $db->table('password_resets')->insert([
            'user_id'    => $user['id'],
            'otp_hash'   => password_hash($code, PASSWORD_DEFAULT),
            'attempts'   => 0,
            'expires_at' => date('Y-m-d H:i:s', time() + self::CODE_TTL_MINUTES * 60),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $resetId = $db->insertID();

        if (!$this->sendCodeEmail($user, $code)) {
            $db->table('password_resets')->where('id', $resetId)->delete();
            session()->setFlashdata('error', 'We could not send the email right now. Please try again later or contact your store administrator.');
            return redirect()->to('/auth/forgot')->withInput();
        }

        session()->setFlashdata('success', $genericMessage);
        return redirect()->to('/auth/reset');
    }

    public function reset()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return view('auth/reset', ['email' => session()->get('reset_email') ?? '']);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $code = preg_replace('/\D/', '', (string) $this->request->getPost('code'));
        $password = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');

        $fail = function (string $message) {
            session()->setFlashdata('error', $message);
            return redirect()->to('/auth/reset')->withInput();
        };

        if (strlen($password) < 8) {
            return $fail('Your new password must be at least 8 characters.');
        }
        if ($password !== $confirm) {
            return $fail('The new password and confirmation do not match.');
        }

        $invalid = 'That code is invalid or has expired. Request a new code if you need one.';
        $user = (new UserModel())->where('email', $email)->where('status', 'active')->first();
        if (!$user || strlen($code) !== 6) {
            return $fail($invalid);
        }

        $db = db_connect();
        $reset = $db->table('password_resets')
            ->where('user_id', $user['id'])
            ->where('used_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if (!$reset) {
            return $fail($invalid);
        }

        if ((int) $reset['attempts'] >= self::MAX_ATTEMPTS) {
            $db->table('password_resets')->where('id', $reset['id'])->update(['used_at' => date('Y-m-d H:i:s')]);
            return $fail('Too many wrong codes. Request a new code to try again.');
        }

        if (!password_verify($code, $reset['otp_hash'])) {
            $db->table('password_resets')->where('id', $reset['id'])->update(['attempts' => (int) $reset['attempts'] + 1]);
            $left = self::MAX_ATTEMPTS - (int) $reset['attempts'] - 1;
            return $fail($left > 0
                ? "That code is not correct. You have {$left} " . ($left === 1 ? 'try' : 'tries') . ' left.'
                : 'Too many wrong codes. Request a new code to try again.');
        }

        (new UserModel())->update($user['id'], [
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('password_resets')->where('user_id', $user['id'])->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        // Not signed in, so write the audit entry directly.
        (new \App\Models\AuditLogModel())->insert([
            'business_id' => $user['business_id'],
            'user_id'     => $user['id'],
            'action'      => 'auth.password_reset',
            'description' => 'User "' . $user['username'] . '" reset their password with an email code',
            'ip_address'  => $this->request->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        session()->remove('reset_email');
        session()->setFlashdata('success', 'Your password was changed. Sign in with your new password.');
        return redirect()->to('/auth');
    }

    private function sendCodeEmail(array $user, string $code): bool
    {
        $config = config('Email');
        if ($config->SMTPHost === '' && $config->protocol === 'smtp') {
            log_message('error', 'Password reset email not sent: SMTP is not configured.');
            return false;
        }

        $emailService = service('email');
        $fromEmail = $config->fromEmail !== '' ? $config->fromEmail : $config->SMTPUser;
        $emailService->setFrom($fromEmail, $config->fromName !== '' ? $config->fromName : 'Gmack POS');
        $emailService->setTo($user['email']);
        $emailService->setSubject('Your Gmack password reset code');
        $emailService->setMailType('html');
        $emailService->setMessage(view('emails/password_reset', [
            'username' => $user['username'],
            'code'     => $code,
            'minutes'  => self::CODE_TTL_MINUTES,
        ]));

        if (!$emailService->send(false)) {
            // Log the mail error only. Never log the code itself.
            // printDebugger([]) returns only the SMTP conversation, without headers/subject/body.
            log_message('error', 'Password reset email failed for user #' . $user['id'] . ': ' . strip_tags($emailService->printDebugger([])));
            return false;
        }
        return true;
    }
}
