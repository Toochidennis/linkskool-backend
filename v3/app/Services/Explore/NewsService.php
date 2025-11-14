<?php

namespace V3\App\Services\Explore;

class NewsService
{
    public function __construct() 
    {
    }

    public function getNews()
    {
        $news = [
            [
                'id' => 0,
                'title' => 'Waec registration has started',
                'content' => 'The registration for WAEC has begun, please visit the school portal for more details.',
                'date_posted' => '2025-02-16 08:00:00',
                'image_url' => 'https://picsum.photos/seed/waec/800/600',
                'category' => 'WAEC',
                'group' => 'latest',
                'user_like' => 1,
                'likes' => 20,
                'comments' => [
                    [
                        'user_id' => 2,
                        'name' => 'John Doe',
                        'comment' => "I'll apply",
                        'date' => '2025-02-16 22:30:20',
                        'profile_url' => 'https://example.com/profiles/2'
                    ]
                ]
            ],

            [
                'id' => 1,
                'title' => 'School reopens after holidays',
                'content' => 'Classes resume on Monday following the holiday break.',
                'date_posted' => '2025-02-17 07:45:00',
                'image_url' => 'https://picsum.photos/seed/schoolreopens/800/600',
                'category' => 'General',
                'group' => 'latest',
                'user_like' => 0,
                'likes' => 35,
                'comments' => []
            ],

            [
                'id' => 2,
                'title' => 'Exam results announced',
                'content' => 'The results for the recent exams have been published.',
                'date_posted' => '2025-02-18 12:00:00',
                'image_url' => 'https://picsum.photos/seed/examresults/800/600',
                'category' => 'JAMB',
                'group' => 'related',
                'user_like' => 1,
                'likes' => 50,
                'comments' => []
            ],

            [
                'id' => 3,
                'title' => 'Parent-teacher meeting scheduled',
                'content' => 'A parent-teacher meeting is scheduled for next week.',
                'date_posted' => '2025-02-19 15:30:00',
                'image_url' => 'https://picsum.photos/seed/parentmeeting/800/600',
                'category' => 'General',
                'group' => 'recommended',
                'user_like' => 0,
                'likes' => 15,
                'comments' => []
            ],

            [
                'id' => 4,
                'title' => 'New library books have arrived',
                'content' => 'The school library has received new books across various genres.',
                'date_posted' => '2025-02-20 10:00:00',
                'image_url' => 'https://picsum.photos/seed/librarybooks2025/800/600',
                'category' => 'General',
                'group' => 'related',
                'user_like' => 1,
                'likes' => 25,
                'comments' => []
            ],

            [
                'id' => 5,
                'title' => 'Sports day announced',
                'content' => 'The annual sports day is scheduled for later this month.',
                'date_posted' => '2025-02-21 09:00:00',
                'image_url' => 'https://picsum.photos/seed/sportsday2025/800/600',
                'category' => 'General',
                'group' => 'latest',
                'user_like' => 1,
                'likes' => 40,
                'comments' => []
            ],

            [
                'id' => 6,
                'title' => 'Art exhibition opens',
                'content' => 'The school art exhibition is now open for viewing.',
                'date_posted' => '2025-02-22 14:00:00',
                'image_url' => 'https://picsum.photos/seed/artexhibition2025/800/600',
                'category' => 'General',
                'group' => 'recommended',
                'user_like' => 0,
                'likes' => 18,
                'comments' => []
            ],

            [
                'id' => 7,
                'title' => 'Science fair winners announced',
                'content' => 'The winners of the annual science fair have been announced.',
                'date_posted' => '2025-02-23 13:30:00',
                'image_url' => 'https://picsum.photos/seed/sciencefair2025/800/600',
                'category' => 'Admission',
                'group' => 'related',
                'user_like' => 1,
                'likes' => 55,
                'comments' => []
            ],

            [
                'id' => 8,
                'title' => 'Scholarship applications now open',
                'content' => 'A new set of scholarships is now open for application. Eligible students should apply early.',
                'date_posted' => '2025-02-24 10:00:00',
                'image_url' => 'https://picsum.photos/seed/scholarship2025/800/600',
                'category' => 'Scholarships',
                'group' => 'latest',
                'user_like' => 1,
                'likes' => 72,
                'comments' => []
            ],

            [
                'id' => 9,
                'title' => 'New cafeteria menu introduced',
                'content' => 'The cafeteria has introduced a healthier menu.',
                'date_posted' => '2025-02-25 12:30:00',
                'image_url' => 'https://picsum.photos/seed/cafeteriamenu2025/800/600',
                'category' => 'General',
                'group' => 'related',
                'user_like' => 1,
                'likes' => 22,
                'comments' => []
            ],

            [
                'id' => 10,
                'title' => 'UTME mock exam tips released',
                'content' => 'Important tips for students preparing for the JAMB UTME mock exam.',
                'date_posted' => '2025-02-26 11:00:00',
                'image_url' => 'https://picsum.photos/seed/jambmock2025/800/600',
                'category' => 'JAMB',
                'group' => 'recommended',
                'user_like' => 0,
                'likes' => 33,
                'comments' => []
            ]
        ];

        // GROUPING (UI DISPLAY)
        $groups = [
            'latest' => [0, 1, 5, 8],
            'related' => [2, 4, 7, 9],
            'recommended' => [3, 6, 10]
        ];

        // CATEGORY FILTERING (REAL CATEGORIES)
        $categories = [
            'WAEC' => [0],
            'JAMB' => [2, 10],
            'Admission' => [7],
            'Scholarships' => [8],
            'General' => [1, 3, 4, 5, 6, 9]
        ];

        return [
            'groups' => $groups,
            'categories' => $categories,
            'news' => $news
        ];
    }
}
