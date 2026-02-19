<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\LessonAttendanceService;

#[Group('/public/learning')]
class LessonAttendanceController extends ExploreBaseController
{
    private LessonAttendanceService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new LessonAttendanceService($this->pdo);
    }

    #[Route('/lessons/{lesson_id}/attendance', 'POST', middleware: ['api'])]
    public function takeLessonAttendance(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'profile_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'course_id' => 'required|integer',
                'marked_by' => 'nullable|integer',
                'remark' => 'nullable|string',
            ]
        );

        $attendanceId = $this->service->takeLessonAttendance($validated);

        if (!$attendanceId) {
            $this->respondError(
                'Failed to take lesson attendance.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Lesson attendance saved successfully.',
                'data' => [
                    'attendance_id' => $attendanceId,
                ],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/lessons/{lesson_id}/attendance', 'GET', middleware: ['api', 'auth'])]
    public function getLessonAttendance(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'lesson_id' => 'required|integer',
                'attendance_date' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:50',
            ]
        );

        $attendance = $this->service->getLessonAttendance(
            (int) $validated['lesson_id'],
            $validated['attendance_date'] ?? null,
            (int) ($validated['page'] ?? 1),
            (int) ($validated['limit'] ?? 25)
        );

        $this->respond(
            [
                'success' => true,
                'data' => $attendance,
            ],
            HttpStatus::OK
        );
    }
}
