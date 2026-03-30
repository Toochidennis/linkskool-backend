<?php
$studentName = htmlspecialchars((string) ($data['student_name']), ENT_QUOTES, 'UTF-8');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$dashboardUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/') . '/dashboard';
$logoUrl = $assetUrl . '/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Linkskool</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:24px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <tr>
                    <td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
                        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Linkskool" width="42" style="display:block;height:auto;border-radius:8px;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 28px 18px 28px;">
                        <h1 style="margin:0 0 10px 0;font-size:28px;font-weight:700;line-height:1.25;color:#0f172a;">Welcome, <?= $studentName ?>!</h1>
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">
                            We're excited to have you join the Linkskool learning community. Your account is now active and ready to use.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 18px 28px;">
                        <div style="background:#f8fafc;border-left:4px solid #2563eb;padding:18px 18px 18px 20px;border-radius:10px;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;font-weight:700;color:#0f172a;">About Linkskool</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;">
                                Linkskool is a modern online learning platform designed to empower students with quality education.
                                Learn at your own pace, access expert instructors, and achieve your educational goals.
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 18px 28px;">
                        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 18px 8px 18px;">
                            <h3 style="margin:0 0 14px 0;font-size:15px;font-weight:700;color:#0f172a;">What You Can Do Now</h3>
                            <ul style="margin:0;padding:0 0 0 18px;font-size:13px;line-height:1.9;color:#475569;">
                                <li style="margin-bottom:10px;">
                                    <strong style="color:#0f172a;">Explore Courses:</strong>
                                    Browse and enroll in courses from world-class instructors.
                                </li>
                                <li style="margin-bottom:10px;">
                                    <strong style="color:#0f172a;">Learn at Your Pace:</strong>
                                    Access lessons, assignments, and exams whenever it suits your schedule.
                                </li>
                                <li style="margin-bottom:10px;">
                                    <strong style="color:#0f172a;">Track Progress:</strong>
                                    Monitor your learning progress with detailed reports and feedback.
                                </li>
                                <li style="margin-bottom:10px;">
                                    <strong style="color:#0f172a;">Manage School Results:</strong>
                                    Keep your academic records organized and manage your school results in one place.
                                </li>
                                <li style="margin-bottom:10px;">
                                    <strong style="color:#0f172a;">Practice CBT Questions:</strong>
                                    Prepare for JAMB, WAEC, and other exams with CBT practice questions and timed tests.
                                </li>
                                <li style="margin-bottom:0;">
                                    <strong style="color:#0f172a;">Earn Certificates:</strong>
                                    Receive certificates upon course completion to showcase your achievements.
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 20px 28px;">
                        <div style="background:#fefce8;border:1px solid #fde68a;border-radius:12px;padding:18px;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;font-weight:700;color:#854d0e;">Getting Started</h3>
                            <ol style="margin:0;padding-left:20px;font-size:14px;line-height:1.9;color:#713f12;">
                                <li style="margin-bottom:6px;">Open your learning dashboard and complete your profile.</li>
                                <li style="margin-bottom:6px;">Browse available courses related to your interests.</li>
                                <li style="margin-bottom:6px;">Enroll in courses that match your learning goals.</li>
                                <li>Start learning and track your progress.</li>
                            </ol>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px 28px;text-align:center;">
                        <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:13px 28px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">
                            Open Learning Dashboard
                        </a>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'Need Help?',
                    'support_message' => "Our support team is here to help! If you have any questions or encounter any issues, don't hesitate to reach out. We're committed to making your learning experience smooth and enjoyable.",
                    'support_padding' => '28px 32px',
                    'social_label' => 'Follow Us',
                    'social_padding' => '20px 32px',
                    'footer_padding' => '20px 32px',
                    'social_icon_size' => 24,
                    'footer_note' => 'You received this email because you just registered on Linkskool.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
