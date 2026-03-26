<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$programName = htmlspecialchars($data['program_name'] ?? '', ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars($data['course_name'], ENT_QUOTES, 'UTF-8');
$cohortDescription = htmlspecialchars($data['cohort_description'] ?? '', ENT_QUOTES, 'UTF-8');
$cohortBenefits = htmlspecialchars($data['cohort_benefits'] ?? '', ENT_QUOTES, 'UTF-8');
$cohortImageUrl = !empty($data['cohort_image_url']) ? htmlspecialchars($data['cohort_image_url'], ENT_QUOTES, 'UTF-8') : '';
$instructorName = htmlspecialchars($data['instructor_name'] ?? '', ENT_QUOTES, 'UTF-8');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$courseUrl = $appBaseUrl . '/learn/course';
$courseQuery = array_filter([
    'course_id' => $data['course_id'] ?? null,
    'cohort_id' => $data['cohort_id'] ?? null,
    'program_id' => $data['program_id'] ?? null,
]);
if (!empty($courseQuery)) {
    $courseUrl .= '?' . http_build_query($courseQuery);
} else {
    $courseUrl = $appBaseUrl . '/dashboard';
}
$safeCourseUrl = htmlspecialchars($courseUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';

// Format dates if they exist
$startDate = !empty($data['cohort_start_date']) ? date('F d, Y', strtotime($data['cohort_start_date'])) : null;
$endDate = !empty($data['cohort_end_date']) ? date('F d, Y', strtotime($data['cohort_end_date'])) : null;

$checkIcon = 'https://img.icons8.com/color/32/ok.png';

if (!function_exists('normalizeEnrollmentCardContent')) {
    function normalizeEnrollmentCardContent(string $content): string
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

$aboutCourseContent = normalizeEnrollmentCardContent((string) ($data['cohort_description'] ?? ''));
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
                    <td align="center" style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;margin:0;height:auto;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:32px 28px;">
                        <!-- Welcome Message -->
                        <h1 style="margin:0 0 6px 0;font-size:28px;color:#0f172a;font-weight:600;">Welcome Aboard!</h1>
                        <p style="margin:0 0 28px 0;font-size:15px;color:#64748b;">Hello <strong><?= $studentName ?></strong>,</p>

                        <!-- Confirmation Message -->
                        <div style="margin:0 0 24px 0;padding:18px;background:#f0fdf4;border:1px solid #d1fae5;border-radius:6px;">
                            <p style="margin:0;font-size:15px;color:#15803d;font-weight:600;">
                                ✓ You're successfully enrolled in <strong><?= $courseName ?></strong>
                            </p>
                        </div>

                        <!-- Course Image (if available) -->
                        <?php if ($cohortImageUrl): ?>
                        <div style="margin:0 0 24px 0;border-radius:6px;overflow:hidden;">
                            <img src="<?= $cohortImageUrl ?>" alt="<?= $courseName ?>" style="width:100%;height:auto;display:block;max-height:280px;object-fit:cover;">
                        </div>
                        <?php endif; ?>

                        <!-- Course Details Card -->
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;">
                                <strong style="color:#0f172a;">Program:</strong> <?= $programName ?>
                            </p>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;">
                                <strong style="color:#0f172a;">Course:</strong> <?= $courseName ?>
                            </p>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;">
                                <strong style="color:#0f172a;">Instructor:</strong> <?= $instructorName ?>
                            </p>
                            <?php if ($startDate || $endDate): ?>
                            <p style="margin:0;font-size:14px;color:#475569;">
                                <strong style="color:#0f172a;">Duration:</strong> 
                                <?php if ($startDate): ?><?= $startDate ?><?php if ($endDate): ?> - <?= $endDate ?><?php endif; ?><?php else: ?><?= $endDate ?? 'TBD' ?><?php endif; ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Course Description -->
                        <?php if ($aboutCourseContent !== ''): ?>
                        <div style="margin:24px 0;padding:20px;background:#f0f9ff;border-left:4px solid #16a34a;border-radius:0 8px 8px 0;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;color:#1e293b;font-weight:600;">About This Course</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;"><?= $aboutCourseContent ?></p>
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
                            <a href="<?= $safeCourseUrl ?>" style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);color:white;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                                Open Course →
                            </a>
                        </div>

                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'Need Help?',
                    'support_message' => 'Our support team is available to assist you with any questions.',
                    'footer_note' => 'Welcome to your learning journey!',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
