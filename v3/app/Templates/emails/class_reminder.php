<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'Class'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$objectives = htmlspecialchars((string)($data['objectives'] ?? ''), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string)($data['author_name'] ?? 'Your Instructor'), ENT_QUOTES, 'UTF-8');

// Format date properly
$lessonDate = isset($data['lesson_date']) ? date('F d, Y', strtotime($data['lesson_date'])) : 'N/A';
$dayOfWeek = isset($data['lesson_date']) ? date('l', strtotime($data['lesson_date'])) : '';

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
    <title>Class Reminder: <?= $lessonTitle ?></title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
                <!-- Header -->
                <tr>
                    <td align="center" style="background:#f8f9fa;padding:20px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="100" style="display:block;margin:0;height:auto;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:32px 28px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 6px 0;font-size:28px;color:#0f172a;font-weight:600;">Class Reminder</h1>
                        <p style="margin:0 0 28px 0;font-size:15px;color:#64748b;">Hi <strong><?= $recipientName ?></strong>,</p>

                        <!-- Class Info Card -->
                        <div style="margin:0 0 28px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <h2 style="margin:0 0 16px 0;font-size:20px;color:#0f172a;font-weight:600;"><?= $lessonTitle ?></h2>
                            <div style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;">
                                <p style="margin:0 0 6px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Course:</strong> <?= $courseName ?>
                                </p>
                                <p style="margin:0 0 6px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Instructor:</strong> <?= $authorName ?>
                                </p>
                                <p style="margin:0;font-size:14px;color:#0f172a;font-weight:600;">
                                    📆 <?= $dayOfWeek ?>, <?= $lessonDate ?>
                                </p>
                            </div>
                        </div>

                        <!-- What You'll Learn -->
                        <?php if ($objectives): ?>
                        <div style="margin:0 0 28px 0;padding:18px;background:#f8fafc;border-left:3px solid #3b82f6;border-radius:4px;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:600;">What You'll Learn</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($objectives) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Preparation -->
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border-radius:6px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:600;">Prepare for Class</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Mark this date and time on your calendar</li>
                                <li style="margin:4px 0;">Prepare your learning space</li>
                                <li style="margin:4px 0;">Have your materials ready</li>
                            </ul>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:0;font-size:13px;color:#64748b;text-align:center;line-height:1.5;">
                            <strong style="color:#475569;">See you in class!</strong><br>
                            Contact us if you have any questions.
                        </p>
                    </td>
                </tr>

                <!-- Social Links Footer -->
                <tr>
                    <td style="background:#f8fafc;padding:20px;text-align:center;border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 12px 0;font-size:12px;font-weight:600;color:#334155;letter-spacing:0.01em;">CONNECT WITH US</p>
                        <a href="https://www.facebook.com/share/1Dwd5kQsgM/" style="text-decoration:none;display:inline-block;margin:0 6px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $facebookIcon ?>" alt="Facebook" width="20" height="20" style="display:block;">
                        </a>
                        <a href="https://x.com/DigitalDreamsNG" style="text-decoration:none;display:inline-block;margin:0 6px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $xIcon ?>" alt="X (Twitter)" width="20" height="20" style="display:block;">
                        </a>
                        <a href="https://www.youtube.com/@digitaldreamsictacademy1353" style="text-decoration:none;display:inline-block;margin:0 6px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $youtubeIcon ?>" alt="YouTube" width="20" height="20" style="display:block;">
                        </a>
                        <a href="https://www.instagram.com/digitaldreamslimited/?hl=en" style="text-decoration:none;display:inline-block;margin:0 6px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $instagramIcon ?>" alt="Instagram" width="20" height="20" style="display:block;">
                        </a>
                    </td>
                </tr>

                <!-- Copyright Footer -->
                <tr>
                    <td style="background:#f8fafc;padding:12px 20px 16px 20px;text-align:center;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0;">
                        <span style="font-weight:500;">© <?= date('Y') ?> Linkskool.</span> All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
