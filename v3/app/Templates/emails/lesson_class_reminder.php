<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'Live Class'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars((string)($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string)($data['author_name'] ?? 'Your Instructor'), ENT_QUOTES, 'UTF-8');

// Time formatting
$lessonDate = isset($data['lesson_date']) ? date('F d, Y', strtotime($data['lesson_date'])) : 'N/A';

// Parse zoom info
$zoomInfo = is_array($data['zoom_info'] ?? null) ? $data['zoom_info'] : json_decode($data['zoom_info'] ?? '{}', true);
$hasZoom = !empty($zoomInfo) && !empty($zoomInfo['url']);
$zoomUrl = $hasZoom ? htmlspecialchars($zoomInfo['url'], ENT_QUOTES, 'UTF-8') : '';
$zoomStartTime = null;
$zoomEndTime = null;

if ($hasZoom && !empty($zoomInfo['start_time'])) {
    $zoomStartTime = date('g:i A', strtotime($zoomInfo['start_time']));
}
if ($hasZoom && !empty($zoomInfo['end_time'])) {
    $zoomEndTime = date('g:i A', strtotime($zoomInfo['end_time']));
}

// Video URLs
$hasYouTube = !empty($data['video_url']);
$youtubeUrl = $hasYouTube ? htmlspecialchars($data['video_url'], ENT_QUOTES, 'UTF-8') : '';
$hasRecordedVideo = !empty($data['recorded_video_url']);
$recordedVideoUrl = $hasRecordedVideo ? htmlspecialchars($data['recorded_video_url'], ENT_QUOTES, 'UTF-8') : '';

$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';
$zoomIconUrl = 'https://img.icons8.com/color/48/zoom.png';
$yIcon = 'https://img.icons8.com/color/48/youtube.png';
$playIcon = 'https://img.icons8.com/color/32/play-button.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Class Reminder: <?= $lessonTitle ?></title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
                <!-- Header with Logo -->
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#059669 0%,#047857 100%);padding:32px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;margin:0;height:auto;">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:40px 36px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 8px 0;font-size:32px;color:#0f172a;font-weight:700;">🎬 Live Class Starting Soon!</h1>
                        <p style="margin:0 0 24px 0;font-size:16px;color:#64748b;">Hello <strong><?= $recipientName ?></strong>,</p>

                        <!-- Lesson Card -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border:2px solid #d1fae5;border-radius:8px;overflow:hidden;background:#f0fdf4;">
                            <tr>
                                <td style="padding:24px;background:linear-gradient(135deg,#059669 0%,#047857 100%);">
                                    <h2 style="margin:0;font-size:24px;color:#ffffff;font-weight:700;"><?= $lessonTitle ?></h2>
                                    <p style="margin:8px 0 0 0;font-size:14px;color:#d1fae5;">Part of <strong><?= $courseName ?></strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:20px 24px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:8px 0;font-size:14px;color:#475569;">
                                                <strong style="color:#1e293b;">Instructor:</strong> <?= $authorName ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0;font-size:14px;color:#475569;">
                                                <strong style="color:#1e293b;">Date:</strong> <?= $lessonDate ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Description -->
                        <?php if ($description): ?>
                        <div style="margin:24px 0;padding:20px;background:#f8fafc;border-left:4px solid #059669;border-radius:0 8px 8px 0;">
                            <h3 style="margin:0 0 8px 0;font-size:15px;color:#1e293b;font-weight:600;">About This Session</h3>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($description) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Join Options -->
                        <div style="margin:32px 0;">
                            <h3 style="margin:0 0 16px 0;font-size:16px;color:#1e293b;font-weight:600;">📡 How to Join</h3>
                            
                            <!-- Zoom Option -->
                            <?php if ($hasZoom): ?>
                            <div style="margin:16px 0;padding:20px;background:#eff6ff;border:2px solid #dbeafe;border-radius:8px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:top;padding-right:12px;">
                                            <img src="<?= $zoomIconUrl ?>" alt="Zoom" width="28" height="28" style="display:block;">
                                        </td>
                                        <td style="vertical-align:top;">
                                            <h4 style="margin:0 0 8px 0;font-size:15px;color:#1e293b;font-weight:600;">Join on Zoom</h4>
                                            <p style="margin:0 0 4px 0;font-size:13px;color:#475569;">
                                                <strong>Time:</strong> 
                                                <?php if ($zoomStartTime): ?>
                                                    <?= $zoomStartTime ?>
                                                    <?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?>
                                                <?php else: ?>
                                                    Check class schedule
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                        <td style="vertical-align:middle;text-align:right;">
                                            <a href="<?= $zoomUrl ?>" style="display:inline-block;padding:8px 16px;background:#2563eb;color:white;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">
                                                Join →
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <?php endif; ?>

                            <!-- YouTube Option -->
                            <?php if ($hasYouTube): ?>
                            <div style="margin:16px 0;padding:20px;background:#fef2f2;border:2px solid #fee2e2;border-radius:8px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:top;padding-right:12px;">
                                            <img src="<?= $yIcon ?>" alt="YouTube" width="28" height="28" style="display:block;">
                                        </td>
                                        <td style="vertical-align:top;">
                                            <h4 style="margin:0 0 8px 0;font-size:15px;color:#1e293b;font-weight:600;">Join via YouTube Live</h4>
                                            <p style="margin:0 0 4px 0;font-size:13px;color:#475569;">
                                                <strong>Time:</strong> 
                                                <?php if ($zoomStartTime): ?>
                                                    <?= $zoomStartTime ?>
                                                    <?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?>
                                                <?php else: ?>
                                                    Check class schedule
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                        <td style="vertical-align:middle;text-align:right;">
                                            <a href="<?= $youtubeUrl ?>" style="display:inline-block;padding:8px 16px;background:#dc2626;color:white;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">
                                                Join →
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Recorded Session -->
                        <?php if ($hasRecordedVideo): ?>
                        <div style="margin:24px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#1e293b;font-weight:600;">📹 Watch Recording</h4>
                            <p style="margin:0 0 12px 0;font-size:13px;color:#475569;">
                                If you missed the live session, you can watch the recorded version.
                            </p>
                            <a href="<?= $recordedVideoUrl ?>" style="display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">
                                <img src="<?= $playIcon ?>" alt="Play" width="16" height="16" style="display:inline;margin-right:6px;vertical-align:middle;">
                                Watch Recording
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Preparation Tips -->
                        <div style="margin:28px 0;padding:20px;background:#f3f4f6;border-radius:8px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#374151;font-weight:600;">✨ Before You Join</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#6b7280;line-height:1.8;">
                                <li style="margin:6px 0;">Make sure your internet connection is stable</li>
                                <li style="margin:6px 0;">Test your camera and microphone (if required)</li>
                                <li style="margin:6px 0;">Join a few minutes early to resolve any technical issues</li>
                                <li style="margin:6px 0;">Have your materials ready if needed</li>
                            </ul>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:28px 0 0 0;font-size:13px;color:#64748b;text-align:center;line-height:1.6;">
                            <strong style="font-weight:600;color:#475569;">Technical Issues?</strong><br>
                            Our support team is ready to help. Contact us immediately if you have any problems joining.
                        </p>
                    </td>
                </tr>

                <!-- Social Links Footer -->
                <tr>
                    <td style="background:#f8fafc;padding:24px 20px;text-align:center;border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 14px 0;font-size:13px;font-weight:600;color:#334155;letter-spacing:0.01em;">CONNECT WITH US</p>
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

                <!-- Copyright Footer -->
                <tr>
                    <td style="background:#f8fafc;padding:16px 20px 24px 20px;text-align:center;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;">
                        <span style="font-weight:500;">© <?= date('Y') ?> Linkskool.</span> All rights reserved.<br>
                        <span style="font-size:11px;margin-top:8px;display:block;">See you in class!</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
