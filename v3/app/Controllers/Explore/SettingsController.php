<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group('/public/cbt/settings')]
class SettingsController extends ExploreBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route('', 'GET', ['api'])]
    public function getSettings(): void
    {
        $this->respond([
            'success' => true,
            'data' => [
                'challenge_duration_limit' => 120,
                'max_exams_per_challenge' => 4,
                'min_questions_per_exam' => 10,
                'passing_score_percentage' => 70,
                'leaderboard_enabled' => true,
                'notification_emails' => false,
                'amount' => 10000, //in naira
                'discount_rate' => 0.50,
                'free_trial_days' => 7,
                'coupon_codes' => ['WELCOME50', 'EXAM20']
            ],
        ]);
    }
}
