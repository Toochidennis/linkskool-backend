<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars($data['course_name'], ENT_QUOTES, 'UTF-8');
$certificateName = htmlspecialchars($data['certificate_name'] ?? $studentName, ENT_QUOTES, 'UTF-8');
$certificateUrl = htmlspecialchars($data['certificate_url'] ?? '', ENT_QUOTES, 'UTF-8');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$completionUrl = $appBaseUrl . '/learn/course-completion';
$completionQuery = array_filter([
    'lessonId' => $data['lesson_id'] ?? null,
    'courseId' => $data['course_id'] ?? null,
    'cohortId' => $data['cohort_id'] ?? null,
    'programId' => $data['program_id'] ?? null,
]);
if (!empty($completionQuery)) {
    $completionUrl .= '?' . http_build_query($completionQuery);
} else {
    $completionUrl = $appBaseUrl . '/dashboard';
}
$safeCompletionUrl = htmlspecialchars($completionUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Completed</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <tr>
                    <td align="center" style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;height:auto;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 28px;">
                        <h2 style="margin:0 0 16px 0;font-size:28px;color:#0f172a;font-weight:600;">Congratulations!</h2>
                        <p style="margin:0 0 10px 0;font-size:15px;color:#64748b;">Hi <strong><?= $studentName ?></strong>,</p>
                        <p style="margin:0 0 10px 0;font-size:15px;color:#64748b;">You have successfully completed <strong><?= $courseName ?></strong>.</p>
                        <p style="margin:0 0 24px 0;font-size:15px;color:#64748b;">Certificate name: <strong><?= $certificateName ?></strong></p>
                        <?php if ($certificateUrl !== ''): ?>
                            <p style="margin:0 0 28px 0;">
                                <a href="<?= $certificateUrl ?>" style="display:inline-block;background:#3b82f6;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:600;font-size:14px;">Download Certificate</a>
                            </p>
                        <?php endif; ?>
                        <p style="margin:0 0 28px 0;">
                            <a href="<?= $safeCompletionUrl ?>" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:600;font-size:14px;">View Completion</a>
                        </p>

                        <p style="margin:0;font-size:13px;color:#64748b;text-align:center;line-height:1.5;">
                            <strong style="font-weight:600;color:#475569;">Have questions?</strong><br>
                            Our support team is available to help you.
                        </p>
                    </td>
                </tr>
                <?php
                $footerData = [
                    'support_title' => 'Have questions?',
                    'support_message' => 'Our support team is available to help you.',
                    'social_label' => 'Follow Us',
                    'footer_note' => null,
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
