<?php
$recipientName = htmlspecialchars((string) ($data['recipient_name'] ?? 'Learner'), ENT_QUOTES, 'UTF-8');
$status = (string) ($data['status'] ?? 'pending');
$planName = htmlspecialchars((string) ($data['plan_name'] ?? 'CBT plan'), ENT_QUOTES, 'UTF-8');
$reference = htmlspecialchars((string) ($data['reference'] ?? ''), ENT_QUOTES, 'UTF-8');
$platform = htmlspecialchars(ucfirst((string) ($data['platform'] ?? '')), ENT_QUOTES, 'UTF-8');
$method = htmlspecialchars(ucfirst((string) ($data['method'] ?? '')), ENT_QUOTES, 'UTF-8');
$paymentMessage = htmlspecialchars((string) ($data['payment_message'] ?? ''), ENT_QUOTES, 'UTF-8');
$paidAt = htmlspecialchars((string) ($data['paid_at'] ?? ''), ENT_QUOTES, 'UTF-8');
$amountKobo = (float) ($data['amount'] ?? 0);
$amountNaira = number_format($amountKobo / 100, 2);
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?? 'https://linkskool.com/assets'), '/');
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$cbtUrl = htmlspecialchars($appBaseUrl . '/cbt', ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';

$isSuccessful = $status === 'success';
$statusLabel = $isSuccessful ? 'Payment Successful' : 'Payment Failed';
$ctaLabel = $isSuccessful ? 'Open CBT' : 'Try CBT Payment Again';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $statusLabel ?></title>
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
                        <h1 style="margin:0 0 8px 0;font-size:28px;line-height:1.25;font-weight:700;color:#0f172a;"><?= $statusLabel ?></h1>
                        <p style="margin:0 0 8px 0;font-size:14px;color:#334155;">Hello <strong><?= $recipientName ?></strong>,</p>
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">
                            <?php if ($isSuccessful): ?>
                            Your payment has been confirmed and a receipt has been generated for your records.
                            <?php else: ?>
                            We could not confirm your payment. Here is a summary of the attempt for your records.
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 20px 28px;">
                        <div style="padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h3 style="margin:0 0 12px 0;font-size:15px;font-weight:700;color:#0f172a;">Receipt Summary</h3>
                            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#475569;">
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Status</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Plan</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;"><?= $planName ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Amount</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;">NGN <?= $amountNaira ?></td>
                                </tr>
                                <?php if ($platform !== ''): ?>
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Platform</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;"><?= $platform ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($method !== ''): ?>
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Method</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;"><?= $method ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($reference !== ''): ?>
                                <tr>
                                    <td style="padding:0 0 10px 0;"><strong style="color:#0f172a;">Reference</strong></td>
                                    <td style="padding:0 0 10px 0;text-align:right;"><?= $reference ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($paidAt !== ''): ?>
                                <tr>
                                    <td style="padding:0;"><strong style="color:#0f172a;"><?= $isSuccessful ? 'Confirmed At' : 'Updated At' ?></strong></td>
                                    <td style="padding:0;text-align:right;"><?= $paidAt ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                            <?php if ($paymentMessage !== ''): ?>
                            <div style="margin-top:12px;font-size:13px;line-height:1.7;color:#64748b;"><?= $paymentMessage ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 28px 28px 28px;text-align:center;">
                        <a href="<?= $cbtUrl ?>" style="display:inline-block;padding:13px 26px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">
                            <?= $ctaLabel ?>
                        </a>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => 'Need Help?',
                    'support_message' => 'Our support team is available if you need help with your payment or subscription.',
                    'footer_note' => $isSuccessful
                        ? 'Keep this email as your payment receipt.'
                        : 'You can start a new checkout anytime from your dashboard.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
