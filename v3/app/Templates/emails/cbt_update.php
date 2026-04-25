<?php
$title = htmlspecialchars((string) ($data['title'] ?? 'CBT Update'), ENT_QUOTES, 'UTF-8');
$authorName = htmlspecialchars((string) ($data['author_name'] ?? 'Linkskool Team'), ENT_QUOTES, 'UTF-8');
$emailBody = trim((string) ($data['email_body'] ?? ''));
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?: 'https://linkskool.com/assets'), '/');
$logoUrl = $assetUrl . '/logo.png';
$updateId = (int) ($data['id'] ?? 0);
$updateUrl = $appBaseUrl . '/cbt-updates' . ($updateId > 0 ? '/' . $updateId : '');
$ctaUrl = htmlspecialchars($updateUrl, ENT_QUOTES, 'UTF-8');
$hasHtmlBody = $emailBody !== strip_tags($emailBody);
$renderedBody = $hasHtmlBody
    ? $emailBody
    : nl2br(htmlspecialchars($emailBody, ENT_QUOTES, 'UTF-8'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        .cbt-update-content {
            font-size: 16px;
            line-height: 1.8;
            color: #1f2937;
        }

        .cbt-update-content p,
        .cbt-update-content ul,
        .cbt-update-content ol,
        .cbt-update-content blockquote {
            margin: 0 0 16px 0;
        }

        .cbt-update-content h1,
        .cbt-update-content h2,
        .cbt-update-content h3,
        .cbt-update-content h4 {
            margin: 0 0 14px 0;
            line-height: 1.35;
            color: #111827;
        }

        .cbt-update-content ul,
        .cbt-update-content ol {
            padding-left: 22px;
        }

        .cbt-update-content li {
            margin-bottom: 8px;
        }

        .cbt-update-content a {
            color: #2563eb;
        }

        .cbt-update-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial, Helvetica, sans-serif;color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f6f7fb;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="padding:20px 28px;background:#111827;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="52" style="display:block;height:auto;">
                    </td>
                </tr>

                <tr>
                    <td style="padding:36px 40px 24px 40px;">
                        <div style="margin:0 0 12px 0;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#2563eb;">
                            CBT Update
                        </div>

                        <h1 style="margin:0 0 16px 0;font-size:30px;line-height:1.3;color:#111827;">
                            <?= $title ?>
                        </h1>

                        <div class="cbt-update-content" style="font-size:16px;line-height:1.8;color:#1f2937;">
                            <?= $renderedBody ?>
                        </div>

                        <div style="margin:28px 0 0 0;padding:24px 0 0 0;border-top:1px solid #e5e7eb;text-align:center;">
                            <p style="margin:0 0 14px 0;font-size:13px;line-height:1.6;color:#6b7280;">
                                Tap below to read more in the app.
                            </p>
                            <a href="<?= $ctaUrl ?>" style="display:inline-block;padding:13px 28px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:700;">
                                Read More
                            </a>
                        </div>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'social_label' => 'Stay Connected',
                    'background' => '#f9fafb',
                    'border_color' => '#e5e7eb',
                    'support_title' => null,
                    'support_message' => null,
                    'footer_note' => 'This CBT update was sent from Linkskool.',
                    'footer_padding' => '16px 40px 24px 40px',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
