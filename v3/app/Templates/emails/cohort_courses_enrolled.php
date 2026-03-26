<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$items = is_array($data['items'] ?? null) ? $data['items'] : [];
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$coursesUrl = $appBaseUrl . '/dashboard';
$safeCoursesUrl = htmlspecialchars($coursesUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';
$courseCount = count($items);
$programNames = array_values(array_unique(array_filter(array_map(static function (array $item): string {
    return trim((string) ($item['program_name'] ?? ''));
}, $items))));
$cohortNames = array_values(array_unique(array_filter(array_map(static function (array $item): string {
    return trim((string) ($item['cohort_name'] ?? ''));
}, $items))));
$summaryProgram = $programNames[0] ?? '';
$summaryCohort = $cohortNames[0] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Enrollments Are Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <tr>
                    <td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="42" style="display:block;margin:0;height:auto;border-radius:8px;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px 28px 28px;">
                        <h1 style="margin:0 0 10px 0;font-size:28px;line-height:1.25;color:#0f172a;font-weight:700;">Multiple Enrollments Confirmed</h1>
                        <p style="margin:0 0 20px 0;font-size:14px;line-height:1.7;color:#64748b;">
                            Hello <strong style="color:#334155;"><?= $studentName ?></strong>, your enrollment for <?= $courseCount ?> course<?= $courseCount === 1 ? '' : 's' ?> was successful. Everything is ready for you in your dashboard.
                        </p>

                        <div style="margin:0 0 20px 0;padding:18px;background:#f0fdf4;border:1px solid #dcfce7;border-radius:12px;">
                            <div style="font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#15803d;margin-bottom:8px;">Enrollment Summary</div>
                            <div style="font-size:24px;line-height:1.3;font-weight:700;color:#166534;"><?= $courseCount ?> course<?= $courseCount === 1 ? '' : 's' ?> added</div>
                            <?php if ($summaryProgram !== '' || $summaryCohort !== ''): ?>
                            <div style="margin-top:8px;font-size:14px;line-height:1.7;color:#166534;">
                                <?php if ($summaryProgram !== ''): ?>Program: <strong><?= htmlspecialchars($summaryProgram, ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
                                <?php if ($summaryProgram !== '' && $summaryCohort !== ''): ?><br><?php endif; ?>
                                <?php if ($summaryCohort !== ''): ?>Cohort: <strong><?= htmlspecialchars($summaryCohort, ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin:0 0 20px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;font-weight:700;color:#0f172a;">Your Courses</h3>
                            <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.9;color:#475569;">
                                <?php foreach ($items as $item): ?>
                                    <li style="margin-bottom:10px;">
                                        <strong style="color:#0f172a;"><?= htmlspecialchars((string) ($item['course_name'] ?? 'Course'), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if (!empty($item['program_name'])): ?>
                                            <span style="color:#64748b;">in <?= htmlspecialchars((string) $item['program_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['cohort_name'])): ?>
                                            <br><span style="font-size:13px;color:#64748b;"><?= htmlspecialchars((string) $item['cohort_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div style="margin:0 0 24px 0;padding:18px;background:#eff6ff;border:1px solid #dbeafe;border-radius:12px;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;font-weight:700;color:#1d4ed8;">What To Do Next</h3>
                            <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.8;color:#475569;">
                                <li style="margin-bottom:6px;">Open your dashboard to see all enrolled courses in one place.</li>
                                <li style="margin-bottom:6px;">Review each course outline and schedule.</li>
                                <li>Start with the course you want to focus on first and track your progress from there.</li>
                            </ul>
                        </div>

                        <div style="text-align:center;">
                            <a href="<?= $safeCoursesUrl ?>" style="display:inline-block;padding:13px 26px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">
                                Open Dashboard
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                $footerData = [
                    'support_title' => 'Need Help?',
                    'support_message' => 'Our support team is available to help you get started with your courses.',
                    'footer_note' => 'Your enrolled courses are waiting for you on Linkskool.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
