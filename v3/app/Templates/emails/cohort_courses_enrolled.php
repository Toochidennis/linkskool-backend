<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$items = is_array($data['items'] ?? null) ? $data['items'] : [];
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$appUrl = htmlspecialchars(getenv('APP_URL2') ?: 'https://linkschoolonline.com/cbt-app', ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/assets/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Enrollments Are Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;margin:0;height:auto;border-radius:6px;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <h1 style="margin:0 0 12px 0;font-size:26px;color:#0f172a;font-weight:600;">Enrollment Confirmed</h1>
                        <p style="margin:0 0 18px 0;font-size:14px;color:#475569;line-height:1.6;">Hello <strong><?= $studentName ?></strong>, your enrollment for <?= count($items) ?> course<?= count($items) === 1 ? '' : 's' ?> was successful.</p>

                        <p style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:600;">Courses:</p>
                        <ul style="margin:0 0 24px 0;padding-left:20px;font-size:14px;color:#475569;line-height:1.8;">
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <strong><?= htmlspecialchars((string) ($item['course_name'] ?? 'Course'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($item['program_name'])): ?>
                                        - <?= htmlspecialchars((string) $item['program_name'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                    <?php if (!empty($item['cohort_name'])): ?>
                                        (<?= htmlspecialchars((string) $item['cohort_name'], ENT_QUOTES, 'UTF-8') ?>)
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <a href="<?= $appUrl ?>" style="display:inline-block;padding:12px 24px;background:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:600;">
                            Open Dashboard
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#64748b;line-height:1.5;">
                            © <?= date('Y') ?> Linkskool. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
