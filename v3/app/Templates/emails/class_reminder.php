<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'Class'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$objectives = htmlspecialchars((string)($data['objectives'] ?? ''), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string)($data['author_name'] ?? 'Your Instructor'), ENT_QUOTES, 'UTF-8');

// Format date properly
$lessonDate = isset($data['lesson_date']) ? date('F d, Y', strtotime($data['lesson_date'])) : 'N/A';
$dayOfWeek = isset($data['lesson_date']) ? date('l', strtotime($data['lesson_date'])) : '';

$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.net';
$appBaseUrl = rtrim((string) (getenv('APP_URL2') ?: 'https://linkschoolonline.com/cbt-app'), '/');
$classUrl = $appBaseUrl . '/learn/class-reminder';
$classQuery = array_filter([
    'lessonId' => $data['id'] ?? null,
    'courseId' => $data['course_id'] ?? null,
    'cohortId' => $data['cohort_id'] ?? null,
    'programId' => $data['program_id'] ?? null,
]);
if (!empty($classQuery)) {
    $classUrl .= '?' . http_build_query($classQuery);
}
$safeClassUrl = htmlspecialchars($classUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/assets/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Reminder: <?= $lessonTitle ?></title>
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
                        <!-- Greeting -->
                        <h1 style="margin:0 0 6px 0;font-size:28px;color:#0f172a;font-weight:600;">Class Reminder</h1>
                        <p style="margin:0 0 28px 0;font-size:15px;color:#64748b;">Hi <strong><?= $recipientName ?></strong>,</p>

                        <!-- Class Info Card -->
                        <div style="margin:0 0 28px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                            <h2 style="margin:0 0 16px 0;font-size:20px;color:#0f172a;font-weight:600;"><?= $lessonTitle ?></h2>
                            <div style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;">
                                <p style="margin:0 0 6px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Course:</strong> <?= $courseName ?>
                                </p>
                                <p style="margin:0 0 6px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Instructor:</strong> <?= $authorName ?>
                                </p>
                                <p style="margin:0;font-size:14px;color:#0f172a;font-weight:600;">
                                    📆 <?= $dayOfWeek ?>, <?= $lessonDate ?>
                                </p>
                            </div>
                        </div>

                        <!-- What You'll Learn -->
                        <?php if ($objectives): ?>
                        <div style="margin:0 0 28px 0;padding:18px;background:#f8fafc;border-left:3px solid #3b82f6;border-radius:4px;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:600;">What You'll Learn</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;">
                                <?= htmlspecialchars_decode($objectives) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Preparation -->
                        <div style="margin:0 0 24px 0;padding:18px;background:#f8fafc;border-radius:6px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:600;">Prepare for Class</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Mark this date and time on your calendar</li>
                                <li style="margin:4px 0;">Prepare your learning space</li>
                                <li style="margin:4px 0;">Have your materials ready</li>
                            </ul>
                        </div>

                        <div style="margin:0 0 24px 0;text-align:center;">
                            <a href="<?= $safeClassUrl ?>" style="display:inline-block;padding:14px 32px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;">
                                Open Class Reminder
                            </a>
                        </div>

                        <!-- Support Message -->
                        <p style="margin:0;font-size:13px;color:#64748b;text-align:center;line-height:1.5;">
                            <strong style="color:#475569;">See you in class!</strong><br>
                            Contact us if you have any questions.
                        </p>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'See you in class!',
                    'support_message' => 'Contact us if you have any questions.',
                    'social_padding' => '20px',
                    'footer_padding' => '12px 20px 16px 20px',
                    'social_icon_size' => 20,
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
