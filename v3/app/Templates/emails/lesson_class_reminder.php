<?php
$studentName = htmlspecialchars($data['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$lessonTitle = htmlspecialchars($data['lesson_title'] ?? 'Upcoming Lesson', ENT_QUOTES, 'UTF-8');
$classDate = htmlspecialchars($data['class_date'] ?? '', ENT_QUOTES, 'UTF-8');
$classTime = htmlspecialchars($data['class_time'] ?? '', ENT_QUOTES, 'UTF-8');
$joinUrl = htmlspecialchars($data['join_url'] ?? '', ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Reminder</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%);padding:28px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px;">
                        <h2 style="margin:0 0 16px 0;font-size:28px;color:#0f172a;">Class Reminder</h2>
                        <p style="margin:0 0 12px 0;">Hi <strong><?= $studentName ?></strong>,</p>
                        <p style="margin:0 0 12px 0;">This is a reminder for your upcoming lesson: <strong><?= $lessonTitle ?></strong>.</p>
                        <p style="margin:0 0 6px 0;"><strong>Date:</strong> <?= $classDate !== '' ? $classDate : 'TBD' ?></p>
                        <p style="margin:0 0 20px 0;"><strong>Time:</strong> <?= $classTime !== '' ? $classTime : 'TBD' ?></p>
                        <?php if ($joinUrl !== ''): ?>
                            <p style="margin:0;">
                                <a href="<?= $joinUrl ?>" style="display:inline-block;background:#0ea5e9;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;">Join Class</a>
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
