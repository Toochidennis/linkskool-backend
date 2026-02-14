<?php
$studentName = htmlspecialchars($data['student_name']);
$score = htmlspecialchars($data['assigned_score']);
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

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.05);border:1px solid #e2e8f0;">

<tr>
<td align="center" style="background:linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);padding:28px 24px;">
    <img src="<?= $logoUrl ?>" alt="Linkskool" width="160" style="display:block;">
</td>
</tr>

<tr>
<td style="padding:40px 36px 32px 36px;line-height:1.7;">

<h2 style="margin:0 0 20px 0;color:#0f172a;font-size:28px;font-weight:700;line-height:1.25;letter-spacing:-0.02em;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">Your Assignment Has Been Graded</h2>

<p style="margin:0 0 16px 0;font-size:16px;color:#334155;">Hello <span style="font-weight:600;color:#1e293b;"><?= $studentName ?></span>,</p>

<p style="margin:0 0 16px 0;font-size:16px;color:#334155;">Your submission for <strong style="font-weight:600;color:#1e293b;"><?= $lessonTitle ?></strong> has been reviewed and graded.</p>

<div style="margin:32px 0;padding:28px;background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border:2px solid #e8edf3;border-radius:10px;text-align:center;">
    <p style="margin:0;font-size:13px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Your Score</p>
    <p style="margin:8px 0 0 0;font-size:42px;font-weight:800;color:#1a73e8;line-height:1;letter-spacing:-0.02em;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
        <?= $score ?><span style="font-size:28px;font-weight:700;">%</span>
    </p>
</div>

<?php if ($remark): ?>
<p style="margin:0 0 18px 0;font-size:16px;color:#334155;"><strong style="font-weight:700;color:#1e293b;font-size:15px;">Remark:</strong><br><span style="color:#475569;font-size:15px;line-height:1.7;"><?= nl2br($remark) ?></span></p>
<?php endif; ?>

<?php if ($comment): ?>
<p style="margin:0 0 18px 0;font-size:16px;color:#334155;"><strong style="font-weight:700;color:#1e293b;font-size:15px;">Instructor Feedback:</strong><br><span style="color:#475569;font-size:15px;line-height:1.7;"><?= nl2br($comment) ?></span></p>
<?php endif; ?>

{% comment %} <div style="margin-top:36px;text-align:center;">
<?php if ($safeViewDetailsUrl !== ''): ?>
    <a href="<?= $safeViewDetailsUrl ?>" 
       style="background:#1a73e8;color:#ffffff;padding:14px 32px;font-size:15px;font-weight:600;
              text-decoration:none;border-radius:8px;display:inline-block;letter-spacing:0.01em;
              box-shadow:0 2px 4px rgba(26,115,232,0.2);font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
        View Details
    </a>
    <p style="margin:16px 0 0 0;font-size:13px;color:#64748b;line-height:1.6;">
        If the button doesn't work, use this link:
        <a href="<?= $safeViewDetailsUrl ?>" style="color:#1a73e8;text-decoration:underline;word-break:break-all;font-weight:500;">
            <?= $safeViewDetailsUrl ?>
        </a>
    </p>
<?php else: ?>
    <p style="margin:0;font-size:14px;color:#64748b;line-height:1.6;">
        View details from your dashboard after signing in.
    </p>
<?php endif; ?>
</div> {% endcomment %}

<p style="margin:40px 0 0 0;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
<strong style="font-weight:600;color:#475569;">Have questions?</strong><br>
Our support team is available to help you with anything you need.
</p>

</td>
</tr>

<tr>
<td style="background:#f8fafc;padding:24px 20px;text-align:center;border-top:1px solid #e2e8f0;">
    <p style="margin:0 0 14px 0;font-size:14px;font-weight:700;color:#334155;letter-spacing:0.01em;">Contact Us</p>
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
