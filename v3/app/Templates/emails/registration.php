<?php
$studentName = htmlspecialchars($data['student_name'], ENT_QUOTES, 'UTF-8');
$assetUrl = getenv('ASSET_URL') ?? 'https://linkskool.net';
$appUrl = getenv('APP_URL2') ?? 'https://linkschoolonline.com/cbt-app';
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
    <title>Welcome to Linkskool</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <!-- Header -->
                <tr>
                    <td align="center" style="background:linear-gradient(135deg,#059669 0%,#047857 100%);padding:36px 24px;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="180" style="display:block;">
                    </td>
                </tr>

                <!-- Welcome Message -->
                <tr>
                    <td style="padding:36px 32px;">
                        <h1 style="margin:0 0 8px 0;font-size:32px;font-weight:700;color:#0f172a;">Welcome, <?= $studentName ?>!</h1>
                        <p style="margin:0 0 24px 0;font-size:16px;color:#475569;line-height:1.6;">
                            We're excited to have you join the Linkskool learning community. Your account is now active and ready to use.
                        </p>
                    </td>
                </tr>

                <!-- What is Linkskool -->
                <tr>
                    <td style="padding:0 32px;">
                        <div style="background:#f0fdf4;border-left:4px solid #059669;padding:20px;border-radius:6px;margin-bottom:24px;">
                            <h3 style="margin:0 0 12px 0;font-size:16px;font-weight:700;color:#065f46;">About Linkskool</h3>
                            <p style="margin:0;font-size:14px;color:#166534;line-height:1.6;">
                                Linkskool is a modern online learning platform designed to empower students with quality education. 
                                Learn at your own pace, access expert instructors, and achieve your educational goals.
                            </p>
                        </div>
                    </td>
                </tr>

                <!-- Key Features -->
                <tr>
                    <td style="padding:0 32px;">
                        <h3 style="margin:0 0 16px 0;font-size:16px;font-weight:700;color:#0f172a;">What You Can Do Now</h3>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;">
                                    <p style="margin:0;font-size:14px;color:#475569;font-weight:600;">📚 Explore Courses</p>
                                    <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Browse and enroll in courses from world-class instructors.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;">
                                    <p style="margin:0;font-size:14px;color:#475569;font-weight:600;">🎓 Learn at Your Pace</p>
                                    <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Access lessons, assignments, and exams whenever it suits your schedule.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;">
                                    <p style="margin:0;font-size:14px;color:#475569;font-weight:600;">📊 Track Progress</p>
                                    <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Monitor your learning progress with detailed reports and feedback.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0;">
                                    <p style="margin:0;font-size:14px;color:#475569;font-weight:600;">🏆 Earn Certificates</p>
                                    <p style="margin:4px 0 0 0;font-size:13px;color:#64748b;">Receive certificates upon course completion to showcase your achievements.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Getting Started -->
                <tr>
                    <td style="padding:0 32px 24px 32px;">
                        <h3 style="margin:0 0 16px 0;font-size:16px;font-weight:700;color:#0f172a;">Getting Started</h3>
                        <ol style="margin:0;padding-left:20px;font-size:14px;color:#475569;line-height:1.8;">
                            <li style="margin-bottom:8px;">Open your learning dashboard and complete your profile</li>
                            <li style="margin-bottom:8px;">Browse available courses related to your interests</li>
                            <li style="margin-bottom:8px;">Enroll in courses that match your learning goals</li>
                            <li>Start learning and track your progress</li>
                        </ol>
                    </td>
                </tr>

                <!-- CTA Button -->
                <tr>
                    <td style="padding:24px 32px;text-align:center;">
                        <a href="<?= $appUrl ?>" style="display:inline-block;background:linear-gradient(135deg,#059669 0%,#047857 100%);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;letter-spacing:0.3px;">
                            Open Learning Dashboard
                        </a>
                    </td>
                </tr>

                <!-- Support Section -->
                <tr>
                    <td style="padding:28px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                        <h4 style="margin:0 0 12px 0;font-size:14px;font-weight:700;color:#0f172a;">Need Help?</h4>
                        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
                            Our support team is here to help! If you have any questions or encounter any issues, 
                            don't hesitate to reach out. We're committed to making your learning experience smooth and enjoyable.
                        </p>
                    </td>
                </tr>

                <!-- Social Links -->
                <tr>
                    <td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 12px 0;font-size:13px;font-weight:700;color:#334155;letter-spacing:0.01em;">Follow Us</p>
                        <div style="margin:0;">
                            <a href="https://www.facebook.com/share/1Dwd5kQsgM/" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                                <img src="<?= $facebookIcon ?>" alt="Facebook" width="24" height="24" style="display:block;">
                            </a>
                            <a href="https://x.com/DigitalDreamsNG" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                                <img src="<?= $xIcon ?>" alt="X (Twitter)" width="24" height="24" style="display:block;">
                            </a>
                            <a href="https://www.youtube.com/@digitaldreamsictacademy1353" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                                <img src="<?= $youtubeIcon ?>" alt="YouTube" width="24" height="24" style="display:block;">
                            </a>
                            <a href="https://www.instagram.com/digitaldreamslimited/?hl=en" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                                <img src="<?= $instagramIcon ?>" alt="Instagram" width="24" height="24" style="display:block;">
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f8fafc;padding:20px 32px;text-align:center;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;">
                        <p style="margin:0 0 8px 0;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                            <span style="font-weight:500;">© <?= date('Y') ?> Linkskool.</span> All rights reserved.
                        </p>
                        <p style="margin:0;font-size:11px;color:#cbd5e1;">
                            You received this email because you just registered on Linkskool.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
