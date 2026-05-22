<?php
$institutionName = htmlspecialchars((string) ($data['institution_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$joinCode        = htmlspecialchars((string) ($data['join_code'] ?? ''), ENT_QUOTES, 'UTF-8');
$assetUrl        = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$logoUrl         = $assetUrl . '/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Classroom is Ready</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(15,23,42,0.08);">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);padding:36px 32px;text-align:center;">
                        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Linkskool" width="44" style="display:block;margin:0 auto 16px auto;height:auto;border-radius:10px;">
                        <h1 style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;">Your Classroom is Live!</h1>
                        <p style="margin:10px 0 0 0;font-size:14px;color:#bfdbfe;">
                            <?= $institutionName ?> has been set up successfully on Linkskool
                        </p>
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td style="padding:32px 32px 0 32px;">
                        <p style="margin:0;font-size:15px;line-height:1.7;color:#475569;">
                            Congratulations! Your institution is ready. Share the join code below with your students and staff so they can connect to <strong style="color:#0f172a;"><?= $institutionName ?></strong> on Linkskool Classroom.
                        </p>
                    </td>
                </tr>

                <!-- Join Code Hero -->
                <tr>
                    <td style="padding:28px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;border:2px dashed #2563eb;border-radius:14px;overflow:hidden;">
                            <tr>
                                <td style="background:#2563eb;padding:10px 24px;text-align:center;">
                                    <p style="margin:0;font-size:11px;font-weight:700;color:#bfdbfe;letter-spacing:0.12em;text-transform:uppercase;">Institution Join Code</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:24px;text-align:center;">
                                    <p style="margin:0 0 6px 0;font-size:13px;color:#64748b;">Share this code with anyone who needs to join</p>
                                    <p style="margin:0;font-size:36px;font-weight:700;letter-spacing:0.18em;color:#1e3a5f;font-family:'Courier New',Courier,monospace;"><?= $joinCode ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 24px 20px 24px;text-align:center;">
                                    <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                                        Students and staff enter this code in the Linkskool app to join your institution.<br>Keep it safe — only share with people you want to grant access.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- What's next -->
                <tr>
                    <td style="padding:0 32px 28px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
                            <tr>
                                <td style="padding:20px 24px 8px 24px;">
                                    <p style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#0f172a;">Next Steps</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 24px 20px 24px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:8px 0;vertical-align:top;">
                                                <table cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="width:28px;vertical-align:top;padding-top:1px;">
                                                            <span style="display:inline-block;width:22px;height:22px;background:#dbeafe;border-radius:50%;text-align:center;font-size:12px;font-weight:700;color:#2563eb;line-height:22px;">1</span>
                                                        </td>
                                                        <td style="padding-left:10px;font-size:13px;color:#475569;line-height:1.6;">
                                                            <strong style="color:#0f172a;">Add your courses</strong> — create courses and assign staff members to them.
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0;vertical-align:top;">
                                                <table cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="width:28px;vertical-align:top;padding-top:1px;">
                                                            <span style="display:inline-block;width:22px;height:22px;background:#dbeafe;border-radius:50%;text-align:center;font-size:12px;font-weight:700;color:#2563eb;line-height:22px;">2</span>
                                                        </td>
                                                        <td style="padding-left:10px;font-size:13px;color:#475569;line-height:1.6;">
                                                            <strong style="color:#0f172a;">Share the join code</strong> — send the code above to your students and staff.
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 0;vertical-align:top;">
                                                <table cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="width:28px;vertical-align:top;padding-top:1px;">
                                                            <span style="display:inline-block;width:22px;height:22px;background:#dbeafe;border-radius:50%;text-align:center;font-size:12px;font-weight:700;color:#2563eb;line-height:22px;">3</span>
                                                        </td>
                                                        <td style="padding-left:10px;font-size:13px;color:#475569;line-height:1.6;">
                                                            <strong style="color:#0f172a;">Go live</strong> — publish lessons, schedule quizzes, and start teaching.
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title'   => 'Need Help?',
                    'support_message' => "Our support team is always available. Reach out anytime and we'll help you get your classroom running smoothly.",
                    'support_padding' => '0 32px 28px 32px',
                    'social_padding'  => '20px 32px',
                    'footer_padding'  => '16px 32px 24px 32px',
                    'social_icon_size' => 24,
                    'footer_note'     => 'You received this email because you just created an institution on Linkskool.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
