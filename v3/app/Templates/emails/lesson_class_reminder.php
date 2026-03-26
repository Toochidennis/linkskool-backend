<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'Live Class'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars((string)($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string)($data['author_name'] ?? ''), ENT_QUOTES, 'UTF-8');

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

$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$liveClassUrl = $appBaseUrl . '/learn/live-class';
$liveClassQuery = array_filter([
    'lesson_id' => $data['id'] ?? null,
    'course_id' => $data['course_id'] ?? null,
    'cohort_id' => $data['cohort_id'] ?? null,
    'program_id' => $data['program_id'] ?? null,
]);
if (!empty($liveClassQuery)) {
    $liveClassUrl .= '?' . http_build_query($liveClassQuery);
}
$safeLiveClassUrl = htmlspecialchars($liveClassUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/assets/logo.png';
$zoomIconUrl = 'https://img.icons8.com/color/48/zoom.png';
$yIcon = 'https://img.icons8.com/color/48/youtube.png';
$playIcon = 'https://img.icons8.com/color/32/play-button.png';

if (!function_exists('normalizeReminderCardContent')) {
    function normalizeReminderCardContent(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = html_entity_decode((string) $content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = strip_tags((string) $content);

        $lines = preg_split('/\r\n|\r|\n/', (string) $content) ?: [];
        $lines = array_map(static function (string $line): string {
            return trim($line);
        }, $lines);
        $lines = array_values(array_filter($lines, static function (string $line): bool {
            return $line !== '';
        }));

        return htmlspecialchars(implode("\n\n", $lines), ENT_QUOTES, 'UTF-8');
    }
}

$sessionDescription = normalizeReminderCardContent((string) ($data['description'] ?? ''));
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
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <!-- Header with Logo -->
                <tr>
                    <td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="42" style="display:block;margin:0;height:auto;border-radius:8px;">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:30px 28px 28px 28px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 8px 0;font-size:28px;line-height:1.25;color:#0f172a;font-weight:700;">Live Class Starting Soon</h1>
                        <p style="margin:0 0 8px 0;font-size:14px;color:#334155;">Hello <strong><?= $recipientName ?></strong>,</p>
                        <p style="margin:0 0 24px 0;font-size:14px;line-height:1.7;color:#64748b;">
                            Your live class is coming up shortly. Here are the details and the quickest ways to join.
                        </p>

                        <!-- Lesson Card -->
                        <div style="margin:0 0 20px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h2 style="margin:0 0 6px 0;font-size:20px;line-height:1.35;color:#0f172a;font-weight:700;"><?= $lessonTitle ?></h2>
                            <p style="margin:0 0 14px 0;font-size:14px;color:#64748b;">Part of <strong style="color:#334155;"><?= $courseName ?></strong></p>
                            <div style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;">
                                <p style="margin:0 0 8px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Instructor:</strong> <?= $authorName ?>
                                </p>
                                <p style="margin:0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Date:</strong> <?= $lessonDate ?>
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if ($sessionDescription !== ''): ?>
                        <div style="margin:0 0 20px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #3b82f6;border-radius:12px;">
                            <h3 style="margin:0 0 8px 0;font-size:15px;color:#0f172a;font-weight:700;">About This Session</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;"><?= $sessionDescription ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Join Options -->
                        <div style="margin:0 0 20px 0;">
                            <h3 style="margin:0 0 14px 0;font-size:16px;color:#0f172a;font-weight:700;">Join Your Class</h3>
                            
                            <!-- Zoom Option -->
                            <?php if ($hasZoom): ?>
                            <div style="margin:0 0 14px 0;padding:18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
                                <div style="margin:0 0 12px 0;">
                                    <img src="<?= $zoomIconUrl ?>" alt="Zoom" width="24" height="24" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                    <h4 style="display:inline-block;vertical-align:middle;margin:0;font-size:15px;color:#0f172a;font-weight:700;">Join on Zoom</h4>
                                </div>
                                <p style="margin:0 0 14px 0;font-size:13px;line-height:1.6;color:#475569;">
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
                            <div style="margin:0 0 14px 0;padding:18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
                                <div style="margin:0 0 12px 0;">
                                    <img src="<?= $yIcon ?>" alt="YouTube" width="24" height="24" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                    <h4 style="display:inline-block;vertical-align:middle;margin:0;font-size:15px;color:#0f172a;font-weight:700;">Join via YouTube Live</h4>
                                </div>
                                <p style="margin:0 0 14px 0;font-size:13px;line-height:1.6;color:#475569;">
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
                        <div style="margin:0 0 20px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h4 style="margin:0 0 8px 0;font-size:14px;color:#0f172a;font-weight:700;">Missed the Live Session?</h4>
                            <p style="margin:0 0 14px 0;font-size:13px;line-height:1.6;color:#475569;">
                                You can watch the recorded version anytime.
                            </p>
                            <a href="<?= $recordedVideoUrl ?>" style="display:inline-block;padding:10px 20px;background:#64748b;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">
                                Watch Recording
                            </a>
                        </div>
                        <?php endif; ?>

                        <div style="margin:0 0 24px 0;text-align:center;">
                            <a href="<?= $safeLiveClassUrl ?>" style="display:inline-block;padding:14px 32px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;">
                                Open Live Class in App
                            </a>
                        </div>

                        <!-- Preparation Tips -->
                        <div style="margin:0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#0f172a;font-weight:700;">Before You Join</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Ensure your internet connection is stable</li>
                                <li style="margin:4px 0;">Test your camera and microphone if required</li>
                                <li style="margin:4px 0;">Join a few minutes early</li>
                                <li style="margin:4px 0;">Have your materials ready</li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'Need help?',
                    'support_message' => 'Contact our support team if you experience any issues.',
                    'footer_note' => 'See you in class!',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
