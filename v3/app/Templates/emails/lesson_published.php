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
                    <td align="center" style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);padding:32px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;margin:0;height:auto;">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:40px 36px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 8px 0;font-size:32px;color:#0f172a;font-weight:700;">Welcome to Your New Lesson! 🎓</h1>
                        <p style="margin:0 0 24px 0;font-size:16px;color:#64748b;">Hello <strong><?= $recipientName ?></strong>,</p>

                        <!-- Lesson Card -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border:2px solid #dbeafe;border-radius:8px;overflow:hidden;background:#f0f9ff;">
                            <tr>
                                <td style="padding:24px;background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);">
                                    <h2 style="margin:0;font-size:24px;color:#ffffff;font-weight:700;"><?= $lessonTitle ?></h2>
                                    <p style="margin:8px 0 0 0;font-size:14px;color:#dbeafe;">Part of <strong><?= $courseName ?></strong></p>
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

                        <!-- Description Section -->
                        <?php if ($description): ?>
                        <div style="margin:32px 0;padding:24px;background:#f8fafc;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
                            <h3 style="margin:0 0 12px 0;font-size:16px;color:#1e293b;font-weight:600;">About This Lesson</h3>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($description) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Goals -->
                        <?php if ($goals): ?>
                        <div style="margin:24px 0;">
                            <h3 style="margin:0 0 12px 0;font-size:16px;color:#1e293b;font-weight:600;">🎯 Learning Goals</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($goals) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Objectives -->
                        <?php if ($objectives): ?>
                        <div style="margin:24px 0;">
                            <h3 style="margin:0 0 12px 0;font-size:16px;color:#1e293b;font-weight:600;">✓ Learning Objectives</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($objectives) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Resources & Content -->
                        <?php if ($hasVideo || $hasMaterial || $hasZoom || $hasAssignment): ?>
                        <div style="margin:32px 0;padding:24px;background:#f0fdf4;border:1px solid #dcfce7;border-radius:8px;">
                            <h3 style="margin:0 0 16px 0;font-size:16px;color:#1e293b;font-weight:600;">📚 Lesson Resources</h3>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <?php if ($hasVideo): ?>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #dcfce7;font-size:14px;">
                                        <div style="display:inline-block;width:24px;vertical-align:middle;">
                                            <img src="<?= $videoIcon ?>" alt="Video" width="20" height="20" style="display:block;">
                                        </div>
                                        <span style="margin-left:8px;color:#1e293b;font-weight:600;">Video Content</span>
                                        <span style="float:right;color:#16a34a;font-weight:600;">✓ Available</span>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($hasMaterial): ?>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #dcfce7;font-size:14px;">
                                        <div style="display:inline-block;width:24px;vertical-align:middle;">
                                            <img src="<?= $documentIcon ?>" alt="Material" width="20" height="20" style="display:block;">
                                        </div>
                                        <span style="margin-left:8px;color:#1e293b;font-weight:600;">Course Material</span>
                                        <span style="float:right;color:#16a34a;font-weight:600;">✓ Available</span>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if ($hasZoom): ?>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #dcfce7;font-size:14px;">
                                        <div style="display:inline-block;width:24px;vertical-align:middle;">
                                            <img src="<?= $zoomIcon ?>" alt="Zoom" width="20" height="20" style="display:block;">
                                        </div>
                                        <span style="margin-left:8px;color:#1e293b;font-weight:600;">Live Session (Zoom)</span>
                                        <span style="float:right;color:#16a34a;font-weight:600;">✓ Scheduled</span>
                                    </td>
                                </tr>
                                <?php if ($zoomStartTime || $zoomEndTime): ?>
                                <tr>
                                    <td style="padding:0 0 12px 32px;font-size:12px;color:#64748b;border-bottom:1px solid #dcfce7;">
                                        <strong>Time:</strong> <?php if ($zoomStartTime): ?><?= $zoomStartTime ?><?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?><?php else: ?>To be confirmed<?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($hasAssignment): ?>
                                <tr>
                                    <td style="padding:12px 0;font-size:14px;">
                                        <div style="display:inline-block;width:24px;vertical-align:middle;">
                                            <img src="<?= $documentIcon ?>" alt="Assignment" width="20" height="20" style="display:block;">
                                        </div>
                                        <span style="margin-left:8px;color:#1e293b;font-weight:600;">Assignment</span>
                                        <?php if ($assignmentDueDate): ?>
                                        <span style="float:right;color:#ea580c;font-weight:600;">Due: <?= $assignmentDueDate ?></span>
                                        <?php else: ?>
                                        <span style="float:right;color:#16a34a;font-weight:600;">✓ Included</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <?php endif; ?>

                        <!-- Assignment Instructions -->
                        <?php if ($hasAssignment): ?>
                        <div style="margin:24px 0;padding:20px;background:#fffbeb;border-left:4px solid #ea580c;border-radius:0 6px 6px 0;">
                            <h4 style="margin:0 0 12px 0;font-size:15px;color:#92400e;font-weight:600;">📝 Assignment Instructions</h4>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#78350f;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($data['assignment_instructions']) ?>
                            </p>
                            <?php if ($assignmentDueDate): ?>
                            <p style="margin:12px 0 0 0;font-size:13px;color:#b45309;font-weight:600;">
                                ⏰ Due Date: <?= $assignmentDueDate ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <div style="margin:32px 0;text-align:center;">
                            <a href="<?= getenv('APP_URL') ?: 'https://linkskool.net' ?>/learn/lessons" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);color:white;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;transition:opacity 0.2s;">
                                Start Learning Now →
                            </a>
                        </div>

                        <!-- Tips Section -->
                        <div style="margin:32px 0;padding:20px;background:#f3f4f6;border-radius:8px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#374151;font-weight:600;">💡 Tips to Get the Most Out of This Lesson</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#6b7280;line-height:1.8;">
                                <li style="margin:6px 0;">Set aside dedicated time to focus on the lesson without distractions</li>
                                <li style="margin:6px 0;">Take notes as you go through the content</li>
                                <li style="margin:6px 0;">Complete all assignments by the due date for comprehensive learning</li>
                                <li style="margin:6px 0;">Reach out to your instructor if you have any questions</li>
                            </ul>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:28px 0 0 0;font-size:13px;color:#64748b;text-align:center;line-height:1.6;">
                            <strong style="font-weight:600;color:#475569;">Questions or Need Help?</strong><br>
                            Our support team is ready to assist you with anything you need.
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
                        <span style="font-size:11px;margin-top:8px;display:block;">This email contains important information about your learning journey.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
