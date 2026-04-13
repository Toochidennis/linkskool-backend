<?php
$userName = htmlspecialchars((string) ($data['user_name'] ?? 'User'), ENT_QUOTES, 'UTF-8');
$resetLink = htmlspecialchars((string) $data['reset_link'], ENT_QUOTES, 'UTF-8');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$logoUrl = $assetUrl . '/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:24px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <tr>
                    <td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
                        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Linkskool" width="42" style="display:block;height:auto;border-radius:8px;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 28px 18px 28px;">
                        <h1 style="margin:0 0 10px 0;font-size:28px;font-weight:700;line-height:1.25;color:#0f172a;">Reset Your Password</h1>
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">
                            Hi <?= $userName ?>, we received a request to reset your password. Click the button below to create a new password.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 24px 28px;text-align:center;">
                        <a href="<?= $resetLink ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:10px;font-size:16px;font-weight:600;border:2px solid #2563eb;transition:all 0.3s ease;">Reset Password</a>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 18px 28px;">
                        <div style="background:#f8fafc;border-left:4px solid #f59e0b;padding:18px 18px 18px 20px;border-radius:10px;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;font-weight:700;color:#0f172a;">Important Security Information</h3>
                            <p style="margin:0 0 10px 0;font-size:13px;line-height:1.7;color:#475569;">
                                This link will expire in <strong>1 hour</strong>. If you don't reset your password within this time, you'll need to request a new link.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#475569;">
                                If you didn't request a password reset, please ignore this email. Your account remains secure.
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px 28px;">
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#64748b;word-break:break-all;">
                            <strong>Or copy and paste this link in your browser:</strong><br>
                            <span style="background:#f1f5f9;padding:8px 12px;border-radius:6px;display:inline-block;margin-top:8px;"><?= $resetLink ?></span>
                        </p>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'Need Help?',
                    'support_message' => 'If you have any questions or need assistance resetting your password, please contact our support team at support@linkskool.com',
                    'footer_note' => 'This link will expire in 1 hour for security purposes.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
