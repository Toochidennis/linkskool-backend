<?php
$recipientName = htmlspecialchars(trim((string)(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?: 'Learner';
$lessonTitle = htmlspecialchars((string)($data['title'] ?? 'New Lesson'), ENT_QUOTES, 'UTF-8');
$courseName = htmlspecialchars((string)($data['course_name'] ?? 'Your Course'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars((string)($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$goals = (string)($data['goals'] ?? '');
$objectives = (string)($data['objectives'] ?? '');
$authorName = htmlspecialchars((string)($data['author_name'] ?? 'Your Instructor'), ENT_QUOTES, 'UTF-8');
$lessonDate = isset($data['lesson_date']) ? date('F d, Y', strtotime($data['lesson_date'])) : 'N/A';
$hasVideo = !empty($data['video_url']) || !empty($data['recorded_video_url']);
$zoomInfo = !empty($data['zoom_info']) ? json_decode($data['zoom_info'], true) : [];
$hasZoom = !empty($zoomInfo) && is_array($zoomInfo) && !empty($zoomInfo['url']);
$zoomStartTime = null;
$zoomEndTime = null;
if ($hasZoom && !empty($zoomInfo['start_time'])) {
    $zoomStartTime = date('g:i A', strtotime($zoomInfo['start_time']));
}
if ($hasZoom && !empty($zoomInfo['end_time'])) {
    $zoomEndTime = date('g:i A', strtotime($zoomInfo['end_time']));
}
$hasAssignment = !empty($data['assignment_instructions']);
$hasMaterial = !empty($data['material_url']);
$assignmentDueDate = isset($data['assignment_due_date']) && !empty($data['assignment_due_date']) 
    ? date('F d, Y', strtotime($data['assignment_due_date'])) 
    : null;
$videoThumbnail = $data['thumbnail'] ?? null;

$assetUrl = getenv('ASSET_URL') ?: 'https://linkskool.com/assets';
$appBaseUrl = rtrim((string) (getenv('APP_URL') ?: 'https://linkskool.com'), '/');
$lessonUrl = $appBaseUrl . '/learn/lesson';
$lessonQuery = array_filter([
    'lesson_id' => $data['id'] ?? null,
    'course_id' => $data['course_id'] ?? null,
    'cohort_id' => $data['cohort_id'] ?? null,
    'program_id' => $data['program_id'] ?? null,
]);
if (!empty($lessonQuery)) {
    $lessonUrl .= '?' . http_build_query($lessonQuery);
}
$safeLessonUrl = htmlspecialchars($lessonUrl, ENT_QUOTES, 'UTF-8');
$logoUrl = $assetUrl . '/logo.png';
$materialUrl = !empty($data['material_url']) ? $assetUrl . '/' . ltrim($data['material_url'], '/') : null;
$assignmentUrl = !empty($data['assignment_url']) ? $assetUrl . '/' . ltrim($data['assignment_url'], '/') : null;
$videoIcon = 'https://img.icons8.com/color/32/play-button.png';
$documentIcon = 'https://img.icons8.com/color/32/document.png';
$zoomIcon = 'https://img.icons8.com/color/32/zoom.png';
$checkIcon = 'https://img.icons8.com/color/32/ok.png';

if (!function_exists('extractBulletItemsFromContent')) {
    function extractBulletItemsFromContent(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        $items = [];

        if (preg_match('/<li\b/i', $content)) {
            preg_match_all('/<li\b[^>]*>(.*?)<\/li>/is', $content, $matches);
            foreach ($matches[1] ?? [] as $match) {
                $line = preg_replace('/<br\s*\/?>/i', "\n", $match);
                $line = html_entity_decode(strip_tags((string) $line), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $line = trim(preg_replace('/\s+/', ' ', $line));
                if ($line !== '') {
                    $items[] = $line;
                }
            }
        } else {
            $normalized = preg_replace('/<br\s*\/?>/i', "\n", $content);
            $normalized = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n", (string) $normalized);
            $normalized = preg_replace('/<\/div>\s*<div[^>]*>/i', "\n", (string) $normalized);
            $normalized = html_entity_decode(strip_tags((string) $normalized), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $lines = preg_split('/\r\n|\r|\n/', $normalized) ?: [];
            foreach ($lines as $line) {
                $line = trim((string) $line);
                $line = preg_replace('/^[\-\*\x{2022}\d\.\)\s]+/u', '', $line);
                $line = trim((string) $line);
                if ($line !== '') {
                    $items[] = $line;
                }
            }
        }

        return array_values(array_unique($items));
    }
}

if (!function_exists('normalizeCardBodyContent')) {
    function normalizeCardBodyContent(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = html_entity_decode((string) $content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = strip_tags((string) $content);

        $lines = preg_split('/\r\n|\r|\n/', (string) $content) ?: [];
        $lines = array_map(static function (string $line): string {
            return trim($line);
        }, $lines);
        $lines = array_values(array_filter($lines, static function (string $line): bool {
            return $line !== '';
        }));

        return htmlspecialchars(implode("\n\n", $lines), ENT_QUOTES, 'UTF-8');
    }
}

$goalItems = extractBulletItemsFromContent($goals);
$objectiveItems = extractBulletItemsFromContent($objectives);
$aboutLessonContent = normalizeCardBodyContent((string) ($data['description'] ?? ''));
$assignmentInstructionContent = normalizeCardBodyContent((string) ($data['assignment_instructions'] ?? ''));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Lesson Published: <?= $lessonTitle ?></title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;background:#f5f7fa;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
                <!-- Header with Logo -->
                <tr>
                    <td align="center" style="background:#f8fafc;padding:18px 24px;border-bottom:1px solid #e2e8f0;">
                        <img src="<?= $logoUrl ?>" alt="Linkskool" width="42" style="display:block;margin:0;height:auto;border-radius:8px;">
                    </td>
                </tr>

                <!-- Main Content -->
                <tr>
                    <td style="padding:30px 28px 28px 28px;">
                        <!-- Greeting -->
                        <h1 style="margin:0 0 8px 0;font-size:28px;line-height:1.25;color:#0f172a;font-weight:700;">New Lesson Published</h1>
                        <p style="margin:0 0 8px 0;font-size:14px;color:#334155;">Hello <strong><?= $recipientName ?></strong>,</p>
                        <p style="margin:0 0 24px 0;font-size:14px;line-height:1.7;color:#64748b;">
                            A new lesson is now available for you. Here is a quick overview of what to expect and how to get started.
                        </p>

                        <!-- Lesson Card -->
                        <div style="margin:0 0 20px 0;padding:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h2 style="margin:0 0 6px 0;font-size:20px;line-height:1.35;color:#0f172a;font-weight:700;"><?= $lessonTitle ?></h2>
                            <p style="margin:0 0 14px 0;font-size:14px;color:#64748b;">Part of <strong style="color:#334155;"><?= $courseName ?></strong></p>
                            <div style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;">
                                <p style="margin:0 0 8px 0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Instructor:</strong> <?= $authorName ?>
                                </p>
                                <p style="margin:0;font-size:14px;color:#475569;">
                                    <strong style="color:#0f172a;">Date:</strong> <?= $lessonDate ?>
                                </p>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <?php if ($aboutLessonContent !== ''): ?>
                        <div style="margin:0 0 20px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #3b82f6;border-radius:12px;">
                            <h3 style="margin:0 0 8px 0;font-size:15px;color:#0f172a;font-weight:700;">About This Lesson</h3>
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#475569;white-space:pre-wrap;"><?= $aboutLessonContent ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Goals -->
                        <?php if (!empty($goalItems)): ?>
                        <div style="margin:0 0 20px 0;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:700;">Learning Goals</h3>
                            <div style="padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.8;color:#475569;">
                                    <?php foreach ($goalItems as $item): ?>
                                    <li style="margin:0 0 8px 0;"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Learning Objectives -->
                        <?php if (!empty($objectiveItems)): ?>
                        <div style="margin:0 0 20px 0;">
                            <h3 style="margin:0 0 10px 0;font-size:15px;color:#0f172a;font-weight:700;">Learning Objectives</h3>
                            <div style="padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.8;color:#475569;">
                                    <?php foreach ($objectiveItems as $item): ?>
                                    <li style="margin:0 0 8px 0;"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Resources & Content -->
                        <?php if ($hasVideo || $hasMaterial || $hasZoom || $hasAssignment): ?>
                        <div style="margin:0 0 20px 0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h3 style="margin:0 0 14px 0;font-size:15px;color:#0f172a;font-weight:700;">Lesson Resources</h3>
                            <div>
                                <?php if ($hasVideo): ?>
                                <div style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-size:14px;line-height:1.6;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Video Content</strong> - Available
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasMaterial): ?>
                                <div style="padding:12px 0;border-bottom:1px solid #e2e8f0;font-size:14px;line-height:1.6;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Course Material</strong> - Available
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasZoom): ?>
                                <div style="padding:12px 0;<?php if ($hasAssignment): ?>border-bottom:1px solid #e2e8f0;<?php endif ?>font-size:14px;line-height:1.6;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Live Session (Zoom)</strong> - Scheduled
                                    <?php if ($zoomStartTime || $zoomEndTime): ?>
                                    <div style="padding:6px 0 0 20px;font-size:13px;line-height:1.6;color:#64748b;">
                                        Time: <?php if ($zoomStartTime): ?><?= $zoomStartTime ?><?php if ($zoomEndTime): ?> - <?= $zoomEndTime ?><?php endif; ?><?php else: ?>To be confirmed<?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($hasAssignment): ?>
                                <div style="padding:12px 0 0 0;font-size:14px;line-height:1.6;color:#475569;">
                                    ✓ <strong style="color:#0f172a;">Assignment</strong> 
                                    <?php if ($assignmentDueDate): ?>- Due: <span style="color:#ea580c;font-weight:600;"><?= $assignmentDueDate ?></span><?php else: ?>- Included<?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Assignment Instructions -->
                        <?php if ($hasAssignment): ?>
                        <div style="margin:0 0 24px 0;padding:18px;background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:12px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:700;">Assignment Instructions</h4>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#78350f;white-space:pre-wrap;"><?= $assignmentInstructionContent ?></p>
                            <?php if ($assignmentDueDate): ?>
                            <p style="margin:12px 0 0 0;font-size:13px;color:#f59e0b;font-weight:600;">
                                Due Date: <?= $assignmentDueDate ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <div style="margin:0 0 24px 0;padding-top:4px;text-align:center;">
                            <a href="<?= $safeLessonUrl ?>" style="display:inline-block;padding:14px 36px;background:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;">
                                Start Learning Now
                            </a>
                        </div>

                        <!-- Tips Section -->
                        <div style="margin:0;padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                            <h4 style="margin:0 0 10px 0;font-size:14px;color:#0f172a;font-weight:700;">Tips for Success</h4>
                            <ul style="margin:0;padding-left:20px;font-size:13px;color:#64748b;line-height:1.8;">
                                <li style="margin:4px 0;">Set aside dedicated time to focus on the lesson</li>
                                <li style="margin:4px 0;">Take notes as you go through the content</li>
                                <li style="margin:4px 0;">Complete assignments by the due date</li>
                                <li style="margin:4px 0;">Reach out to your instructor if you have questions</li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <?php
                $footerData = [
                    'support_title' => null,
                    'support_message' => null,
                    'footer_note' => 'This email contains important information about your learning journey.',
                ];
                include __DIR__ . '/partials/footer.php';
                ?>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
