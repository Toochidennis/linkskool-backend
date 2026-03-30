<?php
$studentName = htmlspecialchars((string) ($data['student_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8');
$score = (int) ($data['assigned_score'] ?? 0);
$remark = htmlspecialchars((string) ($data['remark'] ?? ''), ENT_QUOTES, 'UTF-8');
$comment = htmlspecialchars((string) ($data['comment'] ?? ''), ENT_QUOTES, 'UTF-8');
$lessonTitle = htmlspecialchars((string) ($data['lesson_title'] ?? 'Assignment'), ENT_QUOTES, 'UTF-8');

$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.net/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?? 'https://linkskool.com'), '/');

$resultUrl = $appBaseUrl . '/learn/submission-result';
$resultQuery = array_filter([
    'lesson_id' => $data['lesson_id'] ?? null,
    'profile_id' => $data['profile_id'] ?? null,
    'course_id' => $data['course_id'] ?? null,
    'cohort_id' => $data['cohort_id'] ?? null,
    'program_id' => $data['program_id'] ?? null,
]);

if (!empty($resultQuery)) {
    $resultUrl .= '?' . http_build_query($resultQuery);
}

$safeViewDetailsUrl = htmlspecialchars($resultUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';

$performanceLevel = 'Excellent';
$accent = '#16a34a';
$scoreBg = '#f0fdf4';
$scoreBorder = '#dcfce7';

if ($score < 50) {
    $performanceLevel = 'Needs Improvement';
    $accent = '#dc2626';
    $scoreBg = '#fef2f2';
    $scoreBorder = '#fee2e2';
} elseif ($score < 70) {
    $performanceLevel = 'Good';
    $accent = '#d97706';
    $scoreBg = '#fff7ed';
    $scoreBorder = '#fed7aa';
} elseif ($score < 85) {
    $performanceLevel = 'Very Good';
    $accent = '#2563eb';
    $scoreBg = '#eff6ff';
    $scoreBorder = '#dbeafe';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignment Graded</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:24px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">

<tr>
<td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Linkskool" width="42" style="display:block;height:auto;border-radius:8px;">
</td>
</tr>

<tr>
<td style="padding:28px 28px 18px 28px;">
    <h1 style="margin:0 0 8px 0;font-size:26px;line-height:1.25;font-weight:700;color:#0f172a;">Assignment Graded</h1>
    <p style="margin:0 0 8px 0;font-size:14px;color:#334155;">
        Hello <strong><?= $studentName ?></strong>,
    </p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">
        Your submission for <strong style="color:#0f172a;"><?= $lessonTitle ?></strong> has been reviewed.
    </p>
</td>
</tr>

<tr>
<td style="padding:0 28px 20px 28px;">
    <div style="padding:22px 20px;background:<?= $scoreBg ?>;border:1px solid <?= $scoreBorder ?>;border-radius:12px;text-align:center;">
        <div style="font-size:12px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#64748b;margin-bottom:8px;">Score</div>
        <div style="font-size:42px;line-height:1;font-weight:700;color:<?= $accent ?>;"><?= $score ?>%</div>
        <div style="font-size:14px;color:#475569;margin-top:8px;"><?= htmlspecialchars($performanceLevel, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</td>
</tr>

<?php if ($remark !== ''): ?>
<tr>
<td style="padding:0 28px 16px 28px;">
    <div style="padding:16px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
        <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px;">Remark</div>
        <div style="font-size:14px;line-height:1.7;color:#475569;"><?= nl2br($remark) ?></div>
    </div>
</td>
</tr>
<?php endif; ?>

<?php if ($comment !== ''): ?>
<tr>
<td style="padding:0 28px 20px 28px;">
    <div style="padding:16px 18px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;">
        <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:6px;">Instructor Feedback</div>
        <div style="font-size:14px;line-height:1.7;color:#78350f;"><?= nl2br($comment) ?></div>
    </div>
</td>
</tr>
<?php endif; ?>

<tr>
<td style="padding:0 28px 28px 28px;text-align:center;">
    <a href="<?= $safeViewDetailsUrl ?>" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">
        View Result
    </a>
</td>
</tr>

<?php
$footerData = [
    'support_title' => 'Questions or Need Help?',
    'support_message' => 'Our support team is available to assist you with anything you need.',
    'footer_note' => 'Keep learning and improving your skills.',
];
include __DIR__ . '/partials/footer.php';
?>

</table>

</td>
</tr>
</table>

</body>
</html>
