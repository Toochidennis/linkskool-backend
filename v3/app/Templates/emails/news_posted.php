<?php
$newsTitle = htmlspecialchars((string) ($data['title'] ?? 'News Update'), ENT_QUOTES, 'UTF-8');
$authorName = !empty($data['author_name']) ? htmlspecialchars((string) $data['author_name'], ENT_QUOTES, 'UTF-8') : '';
$content = trim((string) ($data['content'] ?? ''));
$datePosted = !empty($data['date_posted']) ? date('F d, Y', strtotime((string) $data['date_posted'])) : date('F d, Y');
$timePosted = !empty($data['date_posted']) ? date('g:i A', strtotime((string) $data['date_posted'])) : date('g:i A');
$deadline = !empty($data['deadline']) ? date('F d, Y', strtotime((string) $data['deadline'])) : '';
$appUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$newsId = (int) ($data['id'] ?? 0);
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?: 'https://linkskool.com/assets'), '/');
$logoUrl = $assetUrl . '/logo.png';
$newsUrl = $appUrl . '/news' . '/' . $newsId;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $newsTitle ?></title>
</head>
<body style="margin:0;padding:0;background:#f6f6f6;font-family:Georgia, 'Times New Roman', serif;color:#202122;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;background:#f6f6f6;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #d4d4d5;border-radius:12px;overflow:hidden;">
                <!-- Header -->
                <tr>
                    <td style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;height:auto;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    </td>
                </tr>
                
                <!-- News Article Content -->
                <tr>
                    <td style="padding:32px 40px;">
                        <div style="margin:0 0 14px 0;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#3366cc;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                            News Update
                        </div>

                        <!-- News Title -->
                        <h1 style="margin:0 0 18px 0;font-size:32px;line-height:1.3;color:#202122;font-weight:400;font-family:Georgia, 'Times New Roman', serif;">
                            <?= $newsTitle ?>
                        </h1>
                        
                        <!-- Metadata Bar -->
                        <div style="margin:0 0 24px 0;padding:14px 16px;background:#f8f9fa;border:1px solid #e5e5e5;border-radius:8px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:13px;color:#54595d;line-height:1.6;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                                        <?php if ($authorName !== ''): ?>
                                        <span style="display:inline-block;margin-right:12px;">
                                            <strong style="color:#202122;">By</strong> <?= $authorName ?>
                                        </span>
                                        <?php endif; ?>
                                        <span style="display:inline-block;margin-right:12px;">
                                            <strong style="color:#202122;">Published:</strong> <?= htmlspecialchars($datePosted, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span style="display:inline-block;color:#72777d;">
                                            <?= htmlspecialchars($timePosted, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- News Content -->
                        <?php if ($content !== ''): ?>
                        <div style="margin:0 0 24px 0;padding:0 0 8px 0;font-size:16px;line-height:1.75;color:#202122;font-family:Georgia, 'Times New Roman', serif;">
                            <p style="margin:0 0 16px 0;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Deadline Notice (if exists) -->
                        <?php if ($deadline !== ''): ?>
                        <div style="margin:24px 0;padding:16px 20px;background:#f0f4f8;border-left:4px solid #3366cc;border-radius:0 8px 8px 0;">
                            <p style="margin:0;font-size:14px;color:#202122;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                                <strong style="color:#3366cc;">Deadline:</strong> <?= htmlspecialchars($deadline, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Read More Button -->
                        <div style="margin:32px 0 0 0;padding:24px 0 0 0;border-top:1px solid #e5e5e5;text-align:center;">
                            <p style="margin:0 0 14px 0;font-size:13px;line-height:1.6;color:#72777d;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                                Open the full story in the app for more details.
                            </p>
                            <a href="<?= htmlspecialchars($newsUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:12px 32px;background:#3366cc;color:#ffffff;text-decoration:none;font-size:15px;font-weight:500;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;border-radius:4px;">
                                Read Full Article
                            </a>
                        </div>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'social_label' => 'Follow Us',
                    'background' => '#f8f9fa',
                    'border_color' => '#e5e5e5',
                    'support_title' => null,
                    'support_message' => null,
                    'footer_note' => 'This news article was posted on Linkskool News',
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
