<?php
$studentName = htmlspecialchars($data['student_name']);
$score = (int)($data['assigned_score'] ?? 0);
$remark = htmlspecialchars($data['remark'] ?? '');
$comment = htmlspecialchars($data['comment'] ?? '');
$lessonTitle = htmlspecialchars($data['lesson_title']);
$assetUrl = getenv('ASSET_URL') ?? 'https://linkskool.net';
$appUrl = getenv('APP_URL2') ?? 'https://linkschoolonline.com/cbt-app';

$safeViewDetailsUrl = htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/assets/logo.png';
$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';

// Determine performance level based on score
$performanceLevel = 'Excellent';
$performanceColor = '#16a34a'; // green
$performanceBg = '#f0fdf4';
$performanceBorder = '#dcfce7';

if ($score < 50) {
    $performanceLevel = 'Needs Improvement';
    $performanceColor = '#dc2626'; // red
    $performanceBg = '#fef2f2';
    $performanceBorder = '#fee2e2';
} elseif ($score < 70) {
    $performanceLevel = 'Good';
    $performanceColor = '#ea580c'; // orange
    $performanceBg = '#fff7ed';
    $performanceBorder = '#fed7aa';
} elseif ($score < 85) {
    $performanceLevel = 'Very Good';
    $performanceColor = '#2563eb'; // blue
    $performanceBg = '#eff6ff';
    $performanceBorder = '#dbeafe';
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignment Graded</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;color:#1e293b;font-size:16px;line-height:1.6;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:24px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid #e2e8f0;">

<!-- Header -->
<tr>
<td align="center" style="background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);padding:32px 24px;">
    <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;margin:0;height:auto;">
</td>
</tr>

<!-- Main Content -->
<tr>
<td style="padding:40px 36px;">

<!-- Title -->
<h1 style="margin:0 0 12px 0;color:#0f172a;font-size:32px;font-weight:700;line-height:1.25;">🎓 Your Assignment Has Been Graded</h1>

<p style="margin:0 0 24px 0;font-size:16px;color:#64748b;">Hello <strong style="color:#1e293b;"><?= $studentName ?></strong>,</p>

<!-- Assignment Info Card -->
<div style="margin:24px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
    <p style="margin:0;font-size:14px;color:#475569;">
        <strong style="color:#1e293b;">Assignment:</strong> <?= $lessonTitle ?>
    </p>
</div>

<!-- Score Display with Performance Indicator -->
<div style="margin:32px 0;padding:32px 28px;background:linear-gradient(135deg, <?= $performanceBg ?> 0%, rgba(255,255,255,0.5) 100%);border:2px solid <?= $performanceBorder ?>;border-radius:12px;text-align:center;">
    <p style="margin:0 0 8px 0;font-size:12px;font-weight:700;color:<?= $performanceColor ?>;text-transform:uppercase;letter-spacing:0.08em;">Performance Level</p>
    <h2 style="margin:0 0 12px 0;font-size:14px;color:<?= $performanceColor ?>;font-weight:600;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;"><?= $performanceLevel ?></h2>
    <p style="margin:0;font-size:54px;font-weight:800;color:<?= $performanceColor ?>;line-height:1;letter-spacing:-0.02em;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
        <?= $score ?><span style="font-size:32px;font-weight:700;">%</span>
    </p>
    
    <!-- Progress Bar -->
    <div style="margin:20px 0 0 0;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
        <div style="width:<?= $score ?>%;height:100%;background:<?= $performanceColor ?>;border-radius:3px;"></div>
    </div>
</div>

<!-- Remark Section -->
<?php if ($remark): ?>
<div style="margin:28px 0;padding:20px;background:#f8fafc;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
    <h3 style="margin:0 0 10px 0;font-size:15px;color:#1e293b;font-weight:600;">📋 Remark</h3>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
        <?= nl2br($remark) ?>
    </p>
</div>
<?php endif; ?>

<!-- Instructor Feedback Section -->
<?php if ($comment): ?>
<div style="margin:28px 0;padding:20px;background:#fef3c7;border-left:4px solid #d97706;border-radius:0 8px 8px 0;">
    <h3 style="margin:0 0 10px 0;font-size:15px;color:#92400e;font-weight:600;">💬 Instructor Feedback</h3>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#78350f;white-space:pre-wrap;">
        <?= nl2br($comment) ?>
    </p>
</div>
<?php endif; ?>

<!-- Next Steps/Tips -->
<div style="margin:32px 0;padding:20px;background:#f3f4f6;border-radius:8px;">
    <h4 style="margin:0 0 12px 0;font-size:14px;color:#374151;font-weight:600;">📌 Next Steps</h4>
    <ul style="margin:0;padding-left:20px;font-size:13px;color:#6b7280;line-height:1.8;">
        <li style="margin:6px 0;">Review the feedback provided above</li>
        <li style="margin:6px 0;">Work on the areas marked for improvement</li>
        <li style="margin:6px 0;">Reach out to your instructor if you have questions</li>
    </ul>
</div>

<!-- CTA Button -->
<!-- <div style="margin:32px 0;text-align:center;">
    <a href="<?= $safeViewDetailsUrl ?>" 
       style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:white;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;transition:opacity 0.2s;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
        View Full Details →
    </a>
</div> -->

<!-- Support Message -->
<p style="margin:28px 0 0 0;font-size:13px;color:#64748b;text-align:center;line-height:1.6;">
<strong style="font-weight:600;color:#475569;">Questions or Need Help?</strong><br>
Our support team is available to assist you with anything you need.
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
<span style="font-size:11px;margin-top:8px;display:block;">Keep learning and improving your skills!</span>
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
