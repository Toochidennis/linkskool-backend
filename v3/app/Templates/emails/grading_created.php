<?php
$staffName = htmlspecialchars($data['staff_name'] ?? 'Instructor', ENT_QUOTES, 'UTF-8');
$lessonTitle = htmlspecialchars($data['lesson_title'] ?? 'Lesson', ENT_QUOTES, 'UTF-8');
$itemsCount = htmlspecialchars((string) ($data['items_count'] ?? '0'), ENT_QUOTES, 'UTF-8');
$gradedAt = htmlspecialchars($data['graded_at'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Completed</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#16a34a 0%,#166534 100%);padding:28px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px;">
                        <h2 style="margin:0 0 16px 0;font-size:28px;color:#0f172a;">Grading Batch Created</h2>
                        <p style="margin:0 0 12px 0;">Hello <strong><?= $staffName ?></strong>,</p>
                        <p style="margin:0 0 12px 0;">A grading batch has been created for <strong><?= $lessonTitle ?></strong>.</p>
                        <p style="margin:0 0 6px 0;"><strong>Total Submissions:</strong> <?= $itemsCount ?></p>
                        <p style="margin:0;"><strong>Time:</strong> <?= $gradedAt ?></p>
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
