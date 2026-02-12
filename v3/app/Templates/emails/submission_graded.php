<?php
$studentName = htmlspecialchars($data['student_name']);
$score = htmlspecialchars($data['assigned_score']);
$remark = htmlspecialchars($data['remark'] ?? '');
$comment = htmlspecialchars($data['comment'] ?? '');
$lessonTitle = htmlspecialchars($data['lesson_title']);
$assetUrl = getenv('ASSET_URL');
$logoUrl = $assetUrl . '/assets/logo.png';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Assignment Graded</title>
</head>
<body style="margin:0;padding:0;background:#f2f4f6;font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f6;padding:20px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">

<tr>
<td align="center" style="background:#1a73e8;padding:20px;">
    <img src="<?= $logoUrl ?>" alt="LinkSkool" width="150" style="display:block;">
</td>
</tr>

<tr>
<td style="padding:30px;">

<h2 style="margin-top:0;color:#333;">Your Assignment Has Been Graded</h2>

<p>Hello <?= $studentName ?>,</p>

<p>Your submission for <strong><?= $lessonTitle ?></strong> has been reviewed.</p>

<div style="margin:25px 0;padding:20px;background:#f7f9fc;border-radius:6px;text-align:center;">
    <p style="margin:0;font-size:14px;color:#555;">Score</p>
    <p style="margin:5px 0 0 0;font-size:28px;font-weight:bold;color:#1a73e8;">
        <?= $score ?>%
    </p>
</div>

<?php if ($remark): ?>
<p><strong>Remark:</strong><br><?= nl2br($remark) ?></p>
<?php endif; ?>

<?php if ($comment): ?>
<p><strong>Instructor Feedback:</strong><br><?= nl2br($comment) ?></p>
<?php endif; ?>

<div style="margin-top:30px;text-align:center;">
    <a href="<?= $assetUrl ?>" 
       style="background:#1a73e8;color:#ffffff;padding:12px 20px;
              text-decoration:none;border-radius:4px;display:inline-block;">
        View Details
    </a>
</div>

<p style="margin-top:40px;font-size:12px;color:#888;">
If you have questions, contact your instructor through the platform.
</p>

</td>
</tr>

<tr>
<td style="background:#f2f4f6;padding:15px;text-align:center;font-size:12px;color:#888;">
© <?= date('Y') ?> LinkSkool. All rights reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
