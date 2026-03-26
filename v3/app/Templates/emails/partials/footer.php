<?php
$footer = array_merge([
    'support_title' => null,
    'support_message' => null,
    'social_label' => 'CONNECT WITH US',
    'footer_note' => null,
    'show_social' => true,
    'background' => '#f8fafc',
    'border_color' => '#e2e8f0',
    'support_padding' => '0 28px 24px 28px',
    'social_padding' => '24px 20px',
    'footer_padding' => '16px 20px 24px 20px',
    'social_icon_size' => 22,
], $footerData ?? []);

$facebookIcon = 'https://img.icons8.com/color/48/facebook-new.png';
$xIcon = 'https://img.icons8.com/color/48/twitterx--v1.png';
$youtubeIcon = 'https://img.icons8.com/color/48/youtube-play.png';
$instagramIcon = 'https://img.icons8.com/color/48/instagram-new--v1.png';
?>
<?php if (!empty($footer['support_title']) || !empty($footer['support_message'])): ?>
<tr>
<td style="padding:<?= htmlspecialchars((string) $footer['support_padding'], ENT_QUOTES, 'UTF-8') ?>;text-align:center;">
    <p style="margin:0;font-size:13px;color:#64748b;line-height:1.6;">
        <?php if (!empty($footer['support_title'])): ?>
        <strong style="font-weight:600;color:#475569;"><?= htmlspecialchars((string) $footer['support_title'], ENT_QUOTES, 'UTF-8') ?></strong><br>
        <?php endif; ?>
        <?= htmlspecialchars((string) ($footer['support_message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
    </p>
</td>
</tr>
<?php endif; ?>
<?php if (!empty($footer['show_social'])): ?>
<tr>
<td style="background:<?= htmlspecialchars((string) $footer['background'], ENT_QUOTES, 'UTF-8') ?>;padding:<?= htmlspecialchars((string) $footer['social_padding'], ENT_QUOTES, 'UTF-8') ?>;text-align:center;border-top:1px solid <?= htmlspecialchars((string) $footer['border_color'], ENT_QUOTES, 'UTF-8') ?>;">
    <p style="margin:0 0 14px 0;font-size:13px;font-weight:600;color:#334155;letter-spacing:0.01em;"><?= htmlspecialchars((string) $footer['social_label'], ENT_QUOTES, 'UTF-8') ?></p>
    <a href="https://www.facebook.com/share/1Dwd5kQsgM/" style="text-decoration:none;display:inline-block;margin:0 8px;">
        <img src="<?= $facebookIcon ?>" alt="Facebook" width="<?= (int) $footer['social_icon_size'] ?>" height="<?= (int) $footer['social_icon_size'] ?>" style="display:block;">
    </a>
    <a href="https://x.com/DigitalDreamsNG" style="text-decoration:none;display:inline-block;margin:0 8px;">
        <img src="<?= $xIcon ?>" alt="X (Twitter)" width="<?= (int) $footer['social_icon_size'] ?>" height="<?= (int) $footer['social_icon_size'] ?>" style="display:block;">
    </a>
    <a href="https://www.youtube.com/@digitaldreamsictacademy1353" style="text-decoration:none;display:inline-block;margin:0 8px;">
        <img src="<?= $youtubeIcon ?>" alt="YouTube" width="<?= (int) $footer['social_icon_size'] ?>" height="<?= (int) $footer['social_icon_size'] ?>" style="display:block;">
    </a>
    <a href="https://www.instagram.com/digitaldreamslimited/?hl=en" style="text-decoration:none;display:inline-block;margin:0 8px;">
        <img src="<?= $instagramIcon ?>" alt="Instagram" width="<?= (int) $footer['social_icon_size'] ?>" height="<?= (int) $footer['social_icon_size'] ?>" style="display:block;">
    </a>
</td>
</tr>
<?php endif; ?>
<tr>
<td style="background:<?= htmlspecialchars((string) $footer['background'], ENT_QUOTES, 'UTF-8') ?>;padding:<?= htmlspecialchars((string) $footer['footer_padding'], ENT_QUOTES, 'UTF-8') ?>;text-align:center;font-size:12px;color:#94a3b8;border-top:1px solid <?= htmlspecialchars((string) $footer['border_color'], ENT_QUOTES, 'UTF-8') ?>;">
<span style="font-weight:500;">© <?= date('Y') ?> Linkskool.</span> All rights reserved.
<?php if (!empty($footer['footer_note'])): ?><br>
<span style="font-size:11px;margin-top:8px;display:block;"><?= htmlspecialchars((string) $footer['footer_note'], ENT_QUOTES, 'UTF-8') ?></span>
<?php endif; ?>
</td>
</tr>
