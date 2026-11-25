<!doctype html>
<html>
<body style="margin:0;padding:24px;background:#f3f2ef;font-family:Arial,Helvetica,sans-serif;color:#1c2126">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden">
    <tr><td style="background:#1c2126;padding:18px 24px;color:#ffc400;font-weight:bold;letter-spacing:2px;font-size:13px">GMACK POINT OF SALE</td></tr>
    <tr><td style="padding:28px 24px">
      <p style="margin:0 0 12px;font-size:16px">Hi <?= esc($username) ?>,</p>
      <p style="margin:0 0 20px;font-size:15px;line-height:1.5">Use this code to reset your Gmack password:</p>
      <p style="margin:0 0 20px;text-align:center;font-size:34px;font-weight:bold;letter-spacing:8px;color:#ff6b00"><?= esc($code) ?></p>
      <p style="margin:0 0 12px;font-size:14px;color:#5b6168;line-height:1.5">The code expires in <?= (int) $minutes ?> minutes and works only once.</p>
      <p style="margin:0;font-size:14px;color:#5b6168;line-height:1.5">If you didn't ask to reset your password, ignore this email. Your password won't change. Never share this code with anyone, including Gmack staff.</p>
    </td></tr>
  </table>
</body>
</html>
