<?php
$recipientName = htmlspecialchars($data['recipient_name'] ?? 'Learner', ENT_QUOTES, 'UTF-8');
$checkoutItems = htmlspecialchars($data['checkout_items'] ?? 'selected courses', ENT_QUOTES, 'UTF-8');
$reference = htmlspecialchars($data['reference'] ?? '', ENT_QUOTES, 'UTF-8');
$platform = htmlspecialchars(ucfirst((string) ($data['platform'] ?? '')), ENT_QUOTES, 'UTF-8');
$expiresAt = htmlspecialchars($data['expires_at'] ?? '', ENT_QUOTES, 'UTF-8');
$courseId = (int) ($data['course_id'] ?? 0);
$cohortSlug = (string) ($data['cohort_slug'] ?? '');
$amountKobo = (float) ($data['amount'] ?? 0);
$amountNaira = number_format($amountKobo / 100, 2);
$assetUrl = getenv('ASSET_URL') ?? 'https://linkskool.com/assets';
$paymentUrl = 'https://linkskool.com/programs/courses/' . rawurlencode((string) $courseId) . '?ref=' . rawurlencode($cohortSlug);

$logoUrl = $assetUrl . '/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Course Checkout</title>
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
                        <h1 style="margin:0 0 8px 0;font-size:28px;font-weight:600;color:#0f172a;">Your course checkout is still waiting</h1>
                        <p style="margin:0 0 24px 0;font-size:15px;color:#64748b;line-height:1.6;">
                            Hello <strong style="color:#0f172a;"><?= $recipientName ?></strong>, you started a course checkout on Linkskool but it was not completed.
                        </p>

                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:18px 20px;margin-bottom:24px;">
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Courses:</strong> <?= $checkoutItems ?></p>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Amount:</strong> NGN <?= $amountNaira ?></p>
                            <?php if ($platform !== ''): ?>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Platform:</strong> <?= $platform ?></p>
                            <?php endif; ?>
                            <?php if ($reference !== ''): ?>
                            <p style="margin:0 0 8px 0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Reference:</strong> <?= $reference ?></p>
                            <?php endif; ?>
                            <?php if ($expiresAt !== ''): ?>
                            <p style="margin:0;font-size:14px;color:#475569;"><strong style="color:#0f172a;">Expired at:</strong> <?= $expiresAt ?></p>
                            <?php endif; ?>
                        </div>

                        <div style="background:#eff6ff;border-left:4px solid #2563eb;padding:18px 20px;border-radius:6px;margin-bottom:28px;">
                            <p style="margin:0;font-size:14px;color:#1e3a8a;line-height:1.7;">
                                You can return to Linkskool anytime to start a fresh checkout and complete your course enrollment.
                            </p>
                        </div>

                        <div style="text-align:center;padding:8px 0 4px 0;">
                            <a href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;background:linear-gradient(135deg,#059669 0%,#047857 100%);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;">
                                Complete Payment
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                $footerData = [
                    'support_title' => null,
                    'support_message' => null,
                    'social_label' => 'Follow Us',
                    'social_padding' => '20px 32px',
                    'footer_padding' => '20px 32px',
                    'social_icon_size' => 24,
                    'footer_note' => 'You received this email because you started a course checkout on Linkskool.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
