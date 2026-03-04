<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'New Lesson'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars((string)($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$goals = htmlspecialchars((string)($data['goals'] ?? ''), ENT_QUOTES, 'UTF-8');
$objectives = htmlspecialchars((string)($data['objectives'] ?? ''), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string)($data['author_name'] ?? 'Your Instructor'), ENT_QUOTES, 'UTF-8');
$lessonDate = isset($data['lesson_date']) ? date('F d, Y', strtotime($data['lesson_date'])) : 'N/A';
$hasVideo = !empty($data['video_url']) || !empty($data['recorded_video_url']);
$zoomInfo = !empty($data['zoom_info']) ? json_decode($data['zoom_info'], true) : [];
$hasZoom = !empty($zoomInfo) && is_array($zoomInfo) && !empty($zoomInfo['url']);
$zoomStartTime = null;
$zoomEndTime = null;
if ($hasZoom && !empty($zoomInfo['start_time'])) {
    $zoomStartTime = date('g:i A', strtotime($zoomInfo['start_time']));
}
if ($hasZoom && !empty($zoomInfo['end_time'])) {
    $zoomEndTime = date('g:i A', strtotime($zoomInfo['end_time']));
}
$hasAssignment = !empty($data['assignment_instructions']);
$hasMaterial = !empty($data['material_url']);
$assignmentDueDate = isset($data['assignment_due_date']) && !empty($data['assignment_due_date']) 
    ? date('F d, Y', strtotime($data['assignment_due_date'])) 
    : null;
$videoThumbnail = $data['thumbnail'] ?? null;

$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$logoUrl = $assetUrl . '/assets/logo.png';
$materialUrl = !empty($data['material_url']) ? $assetUrl . '/' . ltrim($data['material_url'], '/') : null;
$assignmentUrl = !empty($data['assignment_url']) ? $assetUrl . '/' . ltrim($data['assignment_url'], '/') : null;
$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';
$videoIcon = 'https://img.icons8.com/color/32/play-button.png';
$documentIcon = 'https://img.icons8.com/color/32/document.png';
$zoomIcon = 'https://img.icons8.com/color/32/zoom.png';
$checkIcon = 'https://img.icons8.com/color/32/ok.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Lesson Published: <?= $lessonTitle ?></title>
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
                        <h1 style="margin:0 0 6px 0;font-size:28px;color:#0f172a;font-weight:600;">New Lesson Published</h1>
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

                        <!-- Description Section -->
                        <?php if ($description): ?>
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border-left:3px solid #3b82f6;border-radius:4px;">
                            <h3 style="margin:0 0 8px 0;font-size:15px;color:#0f172a;font-weight:600;">About This Lesson</h3>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($description) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Goals -->
                        <?php if ($goals): ?>
                        <div style="margin:0 0 24px 0;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:600;">Learning Goals</h3>
                            <p style="margin:0;padding:14px;background:#f8fafc;border-radius:4px;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($goals) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Objectives -->
                        <?php if ($objectives): ?>
                        <div style="margin:0 0 24px 0;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:600;">Learning Objectives</h3>
                            <p style="margin:0;padding:14px;background:#f8fafc;border-radius:4px;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($objectives) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Resources & Content -->
                        <?php if ($hasVideo || $hasMaterial || $hasZoom || $hasAssignment): ?>
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <h3 style="margin:0 0 14px 0;font-size:15px;color:#0f172a;font-weight:600;">Lesson Resources</h3>
                            <div>
                                <?php if ($hasVideo): ?>
                                <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Video Content</strong> - Available
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasMaterial): ?>
                                <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Course Material</strong> - Available
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasZoom): ?>
                                <div style="padding:10px 0;<?php if ($hasAssignment): ?>border-bottom:1px solid #e2e8f0;<?php endif ?>font-size:14px;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Live Session (Zoom)</strong> - Scheduled
                                    <?php if ($zoomStartTime || $zoomEndTime): ?>
                                    <div style="padding:6px 0 0 20px;font-size:13px;color:#64748b;">
                                        Time: <?php if ($zoomStartTime): ?><?= $zoomStartTime ?><?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?><?php else: ?>To be confirmed<?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasAssignment): ?>
                                <div style="padding:10px 0;font-size:14px;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Assignment</strong> 
                                    <?php if ($assignmentDueDate): ?>- Due: <span style="color:#ea580c;font-weight:600;"><?= $assignmentDueDate ?></span><?php else: ?>- Included<?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Assignment Instructions -->
                        <?php if ($hasAssignment): ?>
                        <div style="margin:0 0 24px 0;padding:18px;background:#fffbeb;border-left:3px solid #f59e0b;border-radius:4px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:600;">Assignment Instructions</h4>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#78350f;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($data['assignment_instructions']) ?>
                            </p>
                            <?php if ($assignmentDueDate): ?>
                            <p style="margin:12px 0 0 0;font-size:13px;color:#f59e0b;font-weight:600;">
                                Due Date: <?= $assignmentDueDate ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <div style="margin:28px 0;text-align:center;">
                            <a href="<?= getenv('APP_URL') ?: 'https://linkskool.net' ?>/learn/lessons" style="display:inline-block;padding:14px 36px;background:#3b82f6;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;font-size:15px;">
                                Start Learning Now
                            </a>
                        </div>

                        <!-- Tips Section -->
                        <div style="margin:28px 0 0 0;padding:18px;background:#f8fafc;border-radius:6px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:600;">Tips for Success</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Set aside dedicated time to focus on the lesson</li>
                                <li style="margin:4px 0;">Take notes as you go through the content</li>
                                <li style="margin:4px 0;">Complete assignments by the due date</li>
                                <li style="margin:4px 0;">Reach out to your instructor if you have questions</li>
                            </ul>
                        </div>
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
                        <span style="font-size:11px;margin-top:8px;display:block;">This email contains important information about your learning journey.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
