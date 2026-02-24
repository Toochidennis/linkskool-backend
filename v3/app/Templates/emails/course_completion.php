<?php
$studentName = htmlspecialchars($data['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars($data['course_name'] ?? 'Course', ENT_QUOTES, 'UTF-8');
$certificateName = htmlspecialchars($data['certificate_name'] ?? $studentName, ENT_QUOTES, 'UTF-8');
$certificateUrl = htmlspecialchars($data['certificate_url'] ?? '', ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Completed</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#9333ea 0%,#6b21a8 100%);padding:28px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px;">
                        <h2 style="margin:0 0 16px 0;font-size:28px;color:#0f172a;">Congratulations</h2>
                        <p style="margin:0 0 12px 0;">Hi <strong><?= $studentName ?></strong>,</p>
                        <p style="margin:0 0 12px 0;">You have successfully completed <strong><?= $courseName ?></strong>.</p>
                        <p style="margin:0 0 22px 0;">Certificate name: <strong><?= $certificateName ?></strong></p>
                        <?php if ($certificateUrl !== ''): ?>
                            <p style="margin:0;">
                                <a href="<?= $certificateUrl ?>" style="display:inline-block;background:#7e22ce;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;">Download Certificate</a>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;padding:18px 20px;text-align:center;font-size:13px;color:#64748b;">
                        © <?= date('Y') ?> Linkskool. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
