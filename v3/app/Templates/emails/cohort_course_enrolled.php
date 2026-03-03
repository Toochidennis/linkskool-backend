<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$programName = htmlspecialchars($data['program_name'] ?? '', ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars($data['course_name'], ENT_QUOTES, 'UTF-8');
$cohortDescription = htmlspecialchars($data['cohort_description'] ?? '', ENT_QUOTES, 'UTF-8');
$cohortBenefits = htmlspecialchars($data['cohort_benefits'] ?? '', ENT_QUOTES, 'UTF-8');
$cohortImageUrl = !empty($data['cohort_image_url']) ? htmlspecialchars($data['cohort_image_url'], ENT_QUOTES, 'UTF-8') : '';
$instructorName = htmlspecialchars($data['instructor_name'] ?? 'Your Instructor', ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$appUrl = htmlspecialchars(getenv('APP_URL2') ?: 'https://linkschoolonline.com/cbt-app', ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/assets/logo.png';

// Format dates if they exist
$startDate = !empty($data['cohort_start_date']) ? date('F d, Y', strtotime($data['cohort_start_date'])) : null;
$endDate = !empty($data['cohort_end_date']) ? date('F d, Y', strtotime($data['cohort_end_date'])) : null;

$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';
$checkIcon = 'https://img.icons8.com/color/32/ok.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= $courseName ?></title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
                <!-- Header -->
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);padding:32px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;margin:0;height:auto;">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:40px 36px;">
                        <!-- Welcome Message -->
                        <h1 style="margin:0 0 8px 0;font-size:32px;color:#0f172a;font-weight:700;">Welcome Aboard! 🎉</h1>
                        <p style="margin:0 0 28px 0;font-size:16px;color:#64748b;">Hello <strong><?= $studentName ?></strong>,</p>

                        <!-- Confirmation Message -->
                        <div style="margin:24px 0;padding:20px;background:#f0fdf4;border:2px solid #dcfce7;border-radius:8px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:top;padding-right:12px;width:32px;">
                                        <img src="<?= $checkIcon ?>" alt="Success" width="28" height="28" style="display:block;">
                                    </td>
                                    <td style="vertical-align:top;">
                                        <p style="margin:0;font-size:15px;color:#15803d;font-weight:600;">
                                            You're successfully enrolled in <strong><?= $courseName ?></strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Course Image (if available) -->
                        <?php if ($cohortImageUrl): ?>
                        <div style="margin:28px 0;border-radius:8px;overflow:hidden;max-width:100%;">
                            <img src="<?= $cohortImageUrl ?>" alt="<?= $courseName ?>" style="width:100%;height:auto;display:block;max-height:300px;object-fit:cover;">
                        </div>
                        <?php endif; ?>

                        <!-- Course Details Card -->
                        <div style="margin:28px 0;padding:24px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#6b7280;">
                                        <strong style="color:#1e293b;">Program:</strong> <?= $programName ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#6b7280;">
                                        <strong style="color:#1e293b;">Course:</strong> <?= $courseName ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#6b7280;">
                                        <strong style="color:#1e293b;">Instructor:</strong> <?= $instructorName ?>
                                    </td>
                                </tr>
                                <?php if ($startDate || $endDate): ?>
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#6b7280;">
                                        <strong style="color:#1e293b;">Duration:</strong> 
                                        <?php if ($startDate): ?><?= $startDate ?><?php if ($endDate): ?> - <?= $endDate ?><?php endif; ?><?php else: ?><?= $endDate ?? 'TBD' ?><?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Course Description -->
                        <?php if ($cohortDescription): ?>
                        <div style="margin:24px 0;padding:20px;background:#f0f9ff;border-left:4px solid #16a34a;border-radius:0 8px 8px 0;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;color:#1e293b;font-weight:600;">About This Course</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($cohortDescription) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Course Benefits -->
                        <?php if ($cohortBenefits): ?>
                        <div style="margin:24px 0;padding:20px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;color:#92400e;font-weight:600;">What You'll Get</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#78350f;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($cohortBenefits) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Next Steps -->
                        <div style="margin:28px 0;padding:20px;background:#f3f4f6;border-radius:8px;">
                            <h4 style="margin:0 0 12px 0;font-size:14px;color:#374151;font-weight:600;">📌 What's Next?</h4>
                            <ol style="margin:0;padding-left:20px;font-size:13px;color:#6b7280;line-height:1.8;">
                                <li style="margin:6px 0;">Log in to your dashboard to access course materials</li>
                                <li style="margin:6px 0;">Review the course syllabus and schedule</li>
                                <li style="margin:6px 0;">Join the first lesson when it's available</li>
                                <li style="margin:6px 0;">Engage with your instructor and classmates</li>
                            </ol>
                        </div>

                        <!-- CTA Button -->
                        <div style="margin:32px 0;text-align:center;">
                            <a href="<?= $appUrl ?>" style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);color:white;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                                Go to Dashboard →
                            </a>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:28px 0 0 0;font-size:13px;color:#64748b;text-align:center;line-height:1.6;">
                            <strong style="font-weight:600;color:#475569;">Need Help?</strong><br>
                            Our support team is available to assist you with any questions.
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
                        <span style="font-size:11px;margin-top:8px;display:block;">Welcome to your learning journey!</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
