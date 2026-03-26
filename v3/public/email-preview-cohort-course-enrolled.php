<?php

require_once __DIR__ . '/../vendor/autoload.php';

V3\App\Common\Utilities\EnvLoader::load();

$templatePath = __DIR__ . '/../app/Templates/emails/cohort_course_enrolled.php';

$data = [
    'student_name' => 'Dennis Toochi',
    'program_name' => 'Software Development',
    'course_name' => 'Frontend Engineering',
    'cohort_description' => "This course will help you build solid frontend skills with practical lessons, projects, and guided support throughout the cohort.",
    'cohort_benefits' => "Access structured lessons.\nJoin guided classes.\nSubmit assignments and receive feedback.",
    'cohort_image_url' => '',
    'instructor_name' => 'Linkskool Instructor',
    'cohort_start_date' => date('Y-m-d'),
    'cohort_end_date' => date('Y-m-d', strtotime('+10 weeks')),
    'course_id' => 8,
    'cohort_id' => 3,
    'program_id' => 2,
];

echo V3\App\Common\Utilities\TemplateRenderer::render($templatePath, $data);
