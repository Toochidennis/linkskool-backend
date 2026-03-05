<?php
$newsTitle = htmlspecialchars((string) ($data['title'] ?? 'News Update'), ENT_QUOTES, 'UTF-8');
$authorName = !empty($data['author_name']) ? htmlspecialchars((string) $data['author_name'], ENT_QUOTES, 'UTF-8') : '';
$content = trim((string) ($data['content'] ?? ''));
$datePosted = !empty($data['date_posted']) ? date('F d, Y', strtotime((string) $data['date_posted'])) : date('F d, Y');
$timePosted = !empty($data['date_posted']) ? date('g:i A', strtotime((string) $data['date_posted'])) : date('g:i A');
$deadline = !empty($data['deadline']) ? date('F d, Y', strtotime((string) $data['deadline'])) : '';
$appUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.net'), '/');
$assetUrl = rtrim((string) (getenv('ASSET_URL') ?: 'https://linkskool.net'), '/');
$logoUrl = $assetUrl . '/assets/logo.png';
$newsUrl = $appUrl . '/news';
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
    <title><?= $newsTitle ?></title>
</head>
<body style="margin:0;padding:0;background:#f6f6f6;font-family:Georgia, 'Times New Roman', serif;color:#202122;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;background:#f6f6f6;">
    <tr>
        <td align="center">
            <table width="680" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #d4d4d5;max-width:100%;">
                <!-- Header -->
                <tr>
                    <td style="background:#f8f9fa;padding:16px 24px;border-bottom:1px solid #e5e5e5;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="50" style="display:block;height:auto;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    </td>
                </tr>
                
                <!-- News Article Content -->
                <tr>
                    <td style="padding:32px 40px;">
                        <!-- News Title -->
                        <h1 style="margin:0 0 16px 0;font-size:32px;line-height:1.3;color:#202122;font-weight:400;font-family:Georgia, 'Times New Roman', serif;">
                            <?= $newsTitle ?>
                        </h1>
                        
                        <!-- Metadata Bar -->
                        <div style="margin:0 0 24px 0;padding:0 0 16px 0;border-bottom:1px solid #e5e5e5;">
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
                        <div style="margin:0 0 24px 0;font-size:16px;line-height:1.75;color:#202122;font-family:Georgia, 'Times New Roman', serif;">
                            <p style="margin:0 0 16px 0;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Deadline Notice (if exists) -->
                        <?php if ($deadline !== ''): ?>
                        <div style="margin:24px 0;padding:16px 20px;background:#f0f4f8;border-left:4px solid #3366cc;">
                            <p style="margin:0;font-size:14px;color:#202122;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                                <strong style="color:#3366cc;">Deadline:</strong> <?= htmlspecialchars($deadline, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Read More Button -->
                        <div style="margin:32px 0 0 0;padding:24px 0 0 0;border-top:1px solid #e5e5e5;text-align:center;">
                            <a href="<?= htmlspecialchars($newsUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:12px 32px;background:#3366cc;color:#ffffff;text-decoration:none;font-size:15px;font-weight:500;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;border-radius:4px;">
                                Read Full Article
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- Social Media Footer -->
                <tr>
                    <td style="background:#f8f9fa;padding:24px 20px 16px 20px;text-align:center;border-top:1px solid #e5e5e5;">
                        <p style="margin:0 0 14px 0;font-size:13px;font-weight:600;color:#54595d;letter-spacing:0.01em;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">Follow Us</p>
                        <a href="https://www.facebook.com/share/1Dwd5kQsgM/" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $facebookIcon ?>" alt="Facebook" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://x.com/DigitalDreamsNG" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $xIcon ?>" alt="X (Twitter)" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://www.youtube.com/@digitaldreamsictacademy1353" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $youtubeIcon ?>" alt="YouTube" width="22" height="22" style="display:block;">
                        </a>
                        <a href="https://www.instagram.com/digitaldreamslimited/?hl=en" style="text-decoration:none;display:inline-block;margin:0 8px;opacity:0.8;transition:opacity 0.2s;">
                            <img src="<?= $instagramIcon ?>" alt="Instagram" width="22" height="22" style="display:block;">
                        </a>
                    </td>
                </tr>
                <!-- Footer -->
                <tr>
                    <td style="padding:16px 40px 24px 40px;background:#f8f9fa;text-align:center;border-top:1px solid #e5e5e5;">
                        <p style="margin:0 0 4px 0;font-size:12px;color:#72777d;line-height:1.5;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                            This news article was posted on Linkskool News
                        </p>
                        <p style="margin:0;font-size:11px;color:#94a3b8;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
                            © <?= date('Y') ?> Linkskool. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
