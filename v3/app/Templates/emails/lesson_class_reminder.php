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
                    <td align="center" style="background:#f8f9fa;padding:20px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="100" style="display:block;margin:0;height:auto;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:32px 28px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 6px 0;font-size:28px;color:#0f172a;font-weight:600;">🎬 Live Class Starting Soon!</h1>
                        <p style="margin:0 0 28px 0;font-size:15px;color:#64748b;">Hello <strong><?= $recipientName ?></strong>,</p>

                        <!-- Lesson Card -->
                        <div style="margin:0 0 28px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <h2 style="margin:0 0 6px 0;font-size:20px;color:#0f172a;font-weight:600;"><?= $lessonTitle ?></h2>
                            <p style="margin:0 0 16px 0;font-size:14px;color:#64748b;">Part of <strong><?= $courseName ?></strong></p>
                            <div style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;">
                                <p style="margin:0 0 6px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Instructor:</strong> <?= $authorName ?>
                                </p>
                                <p style="margin:0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Date:</strong> <?= $lessonDate ?>
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if ($description): ?>
                        <div style="margin:0 0 28px 0;padding:18px;background:#f8fafc;border-left:3px solid #3b82f6;border-radius:4px;">
                            <h3 style="margin:0 0 8px 0;font-size:15px;color:#0f172a;font-weight:600;">About This Session</h3>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($description) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Join Options -->
                        <div style="margin:0 0 28px 0;">
                            <h3 style="margin:0 0 16px 0;font-size:16px;color:#0f172a;font-weight:600;">Join Your Class</h3>
                            
                            <!-- Zoom Option -->
                            <?php if ($hasZoom): ?>
                            <div style="margin:0 0 16px 0;padding:18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;">
                                <div style="margin:0 0 12px 0;">
                                    <img src="<?= $zoomIconUrl ?>" alt="Zoom" width="24" height="24" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                    <h4 style="display:inline-block;vertical-align:middle;margin:0;font-size:15px;color:#0f172a;font-weight:600;">Join on Zoom</h4>
                                </div>
                                <p style="margin:0 0 14px 0;font-size:13px;color:#475569;">
                                    <strong>Time:</strong> 
                                    <?php if ($zoomStartTime): ?>
                                        <?= $zoomStartTime ?>
                                        <?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?>
                                    <?php else: ?>
                                        Check class schedule
                                    <?php endif; ?>
                                </p>
                                <a href="<?= $zoomUrl ?>" style="display:inline-block;width:100%;padding:12px 0;background:#3b82f6;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;text-align:center;box-sizing:border-box;">
                                    Join Zoom Session
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- YouTube Option -->
                            <?php if ($hasYouTube): ?>
                            <div style="margin:0 0 16px 0;padding:18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;">
                                <div style="margin:0 0 12px 0;">
                                    <img src="<?= $yIcon ?>" alt="YouTube" width="24" height="24" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                    <h4 style="display:inline-block;vertical-align:middle;margin:0;font-size:15px;color:#0f172a;font-weight:600;">Join via YouTube Live</h4>
                                </div>
                                <p style="margin:0 0 14px 0;font-size:13px;color:#475569;">
                                    <strong>Time:</strong> 
                                    <?php if ($zoomStartTime): ?>
                                        <?= $zoomStartTime ?>
                                        <?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?>
                                    <?php else: ?>
                                        Check class schedule
                                    <?php endif; ?>
                                </p>
                                <a href="<?= $youtubeUrl ?>" style="display:inline-block;width:100%;padding:12px 0;background:#3b82f6;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:14px;text-align:center;box-sizing:border-box;">
                                    Join YouTube Live
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Recorded Session -->
                        <?php if ($hasRecordedVideo): ?>
                        <div style="margin:0 0 28px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <h4 style="margin:0 0 8px 0;font-size:14px;color:#0f172a;font-weight:600;">📹 Missed the Live Session?</h4>
                            <p style="margin:0 0 14px 0;font-size:13px;color:#475569;">
                                You can watch the recorded version anytime.
                            </p>
                            <a href="<?= $recordedVideoUrl ?>" style="display:inline-block;padding:10px 20px;background:#64748b;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">
                                Watch Recording
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Preparation Tips -->
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border-radius:6px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#0f172a;font-weight:600;">Before You Join</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Ensure your internet connection is stable</li>
                                <li style="margin:4px 0;">Test your camera and microphone if required</li>
                                <li style="margin:4px 0;">Join a few minutes early</li>
                                <li style="margin:4px 0;">Have your materials ready</li>
                            </ul>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:0;font-size:13px;color:#64748b;text-align:center;line-height:1.5;">
                            <strong style="color:#475569;">Need help?</strong><br>
                            Contact our support team if you experience any issues.
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
