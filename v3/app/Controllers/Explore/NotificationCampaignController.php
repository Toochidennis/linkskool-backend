<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\NotificationCampaignService;

#[Group('/public')]
class NotificationCampaignController extends ExploreBaseController
{
    private NotificationCampaignService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new NotificationCampaignService($this->pdo);
    }

    #[Route('/notification-campaigns/options', 'GET', ['api', 'auth', 'role:admin'])]
    public function getOptions(): void
    {
        $this->respond([
            'success' => true,
            'message' => 'Notification campaign options retrieved successfully',
            'data' => $this->service->getOptions(),
        ]);
    }

    #[Route('/notification-campaigns/send', 'POST', ['api', 'auth', 'role:admin'])]
    public function send(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'mode' => 'required|string|in:reminder',
                'audience' => 'required|string|in:dropped_subscription_cart_users,dropped_course_cohort_cart_users,all_enrolled_users,program_enrolled_users,cohort_enrolled_users',
                'reminder_type' => 'required|string|in:abandoned_cart,abandoned_course_cohort_cart,class_reminder,live_class_reminder,assignment_due_reminder',
                'program_id' => 'nullable|integer',
                'cohort_id' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'minutes_since_abandoned' => 'nullable|integer',
                'cadence' => 'nullable|string|in:generic,one_hour',
            ]
        );

        try {
            $result = $this->service->send($data);
        } catch (\InvalidArgumentException $e) {
            $this->respondError($e->getMessage(), HttpStatus::BAD_REQUEST);
            return;
        }

        $this->respond([
            'success' => true,
            'message' => 'Notification campaign dispatched successfully',
            'data' => $result,
        ]);
    }
}
