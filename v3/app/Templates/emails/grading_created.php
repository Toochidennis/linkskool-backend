<?php
$staffName = htmlspecialchars($data['staff_name'] ?? 'Instructor', ENT_QUOTES, 'UTF-8');
$lessonTitle = htmlspecialchars($data['lesson_title'] ?? 'Lesson', ENT_QUOTES, 'UTF-8');
$itemsCount = htmlspecialchars((string) ($data['items_count'] ?? '0'), ENT_QUOTES, 'UTF-8');
$gradedAt = htmlspecialchars($data['graded_at'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';
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
                    <td align="center" style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;height:auto;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 28px;">
                        <h2 style="margin:0 0 16px 0;font-size:28px;color:#0f172a;font-weight:600;">Grading Batch Created</h2>
                        <p style="margin:0 0 10px 0;font-size:15px;color:#64748b;">Hello <strong><?= $staffName ?></strong>,</p>
                        <p style="margin:0 0 24px 0;font-size:15px;color:#64748b;">A grading batch has been created for <strong><?= $lessonTitle ?></strong>.</p>
                        <div style="padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:24px;">
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Total Submissions:</strong> <?= $itemsCount ?></p>
                            <p style="margin:0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Time:</strong> <?= $gradedAt ?></p>
                        </div>

                        <p style="margin:0;font-size:13px;color:#64748b;text-align:center;line-height:1.5;">
                            <strong style="font-weight:600;color:#475569;">Have questions?</strong><br>
                            Our support team is available to help you.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;padding:24px 20px;text-align:center;border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 14px 0;font-size:13px;font-weight:600;color:#334155;letter-spacing:0.01em;">Follow Us</p>
                        <a href="https://www.facebook.com/share/1Dwd5kQsgM/" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $facebookIcon ?>" alt="Facebook" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://x.com/DigitalDreamsNG" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $xIcon ?>" alt="X (Twitter)" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://www.youtube.com/@digitaldreamsictacademy1353" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $youtubeIcon ?>" alt="YouTube" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://www.instagram.com/digitaldreamslimited/?hl=en" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $instagramIcon ?>" alt="Instagram" width="22" height="22" style="display:block;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;padding:16px 20px 24px 20px;text-align:center;font-size:13px;color:#94a3b8;border-top:1px solid #e2e8f0;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                        <span style="font-weight:500;">© <?= date('Y') ?> Linkskool.</span> All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
