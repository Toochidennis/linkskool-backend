<?php

namespace V3\App\Services\Explore;

class NewsService
{
    public function __construct()
    {
    }

    public function getNews()
    {
        return [
            [
                'id' => 0,
                'title' => 'Waec registration has started',
                'content' => 'The registration for WAEC has begun, please visit the school portal for more details.',
                'date_posted' => '2025-02-16 08:00:00',
                'image_url' => 'https://picsum.photos/seed/waec/800/600',
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
                'content' => 'Classes resume on Monday following the holiday break. Please check your timetable.',
                'date_posted' => '2025-02-17 07:45:00',
                'image_url' => 'https://picsum.photos/seed/schoolreopens/800/600',
                'user_like' => 0,
                'likes' => 35,
                'comments' => [
                    [
                        'user_id' => 3,
                        'name' => 'Alice Smith',
                        'comment' => 'Looking forward to the new term!',
                        'date' => '2025-02-17 09:15:30',
                        'profile_url' => 'https://example.com/profiles/3'
                    ],
                    [
                        'user_id' => 4,
                        'name' => 'Bob Johnson',
                        'comment' => 'Hope everyone had a restful break.',
                        'date' => '2025-02-17 10:00:00',
                        'profile_url' => 'https://example.com/profiles/4'
                    ]
                ]
            ],
            [
                'id' => 2,
                'title' => 'Exam results announced',
                'content' => 'The results for the recent exams have been published. Students can check the online portal.',
                'date_posted' => '2025-02-18 12:00:00',
                'image_url' => 'https://picsum.photos/seed/examresults/800/600',
                'user_like' => 1,
                'likes' => 50,
                'comments' => [
                    [
                        'user_id' => 5,
                        'name' => 'Charlie Brown',
                        'comment' => 'Great results overall!',
                        'date' => '2025-02-18 14:20:10',
                        'profile_url' => 'https://example.com/profiles/5'
                    ]
                ]
            ],
            [
                'id' => 3,
                'title' => 'Parent-teacher meeting scheduled',
                'content' => 'A parent-teacher meeting is scheduled for next week. Please check your email for details.',
                'date_posted' => '2025-02-19 15:30:00',
                'image_url' => 'https://picsum.photos/seed/parentmeeting/800/600',
                'user_like' => 0,
                'likes' => 15,
                'comments' => []
            ],
            [
                'id' => 4,
                'title' => 'New library books have arrived',
                'content' => 'The school library has received new books across various genres. Visit the library for more.',
                'date_posted' => '2025-02-20 10:00:00',
                'image_url' => 'https://picsum.photos/seed/librarybooks/800/600',
                'user_like' => 1,
                'likes' => 25,
                'comments' => [
                    [
                        'user_id' => 6,
                        'name' => 'Diana Prince',
                        'comment' => "Can't wait to check them out!",
                        'date' => '2025-02-20 11:15:45',
                        'profile_url' => 'https://example.com/profiles/6'
                    ]
                ]
            ],
            [
                'id' => 5,
                'title' => 'Sports day announced',
                'content' => 'The annual sports day is scheduled for the end of this month. Get ready for exciting competitions!',
                'date_posted' => '2025-02-21 09:00:00',
                'image_url' => 'https://picsum.photos/seed/sportsday/800/600',
                'user_like' => 1,
                'likes' => 40,
                'comments' => [
                    [
                        'user_id' => 7,
                        'name' => 'Ethan Hunt',
                        'comment' => 'Looking forward to the events.',
                        'date' => '2025-02-21 10:45:00',
                        'profile_url' => 'https://example.com/profiles/7'
                    ],
                    [
                        'user_id' => 8,
                        'name' => 'Fiona Gallagher',
                        'comment' => 'Who will win the relay race?',
                        'date' => '2025-02-21 11:00:00',
                        'profile_url' => 'https://example.com/profiles/8'
                    ]
                ]
            ],
            [
                'id' => 6,
                'title' => 'Art exhibition opens',
                'content' => 'The school art exhibition is now open. Visit the gallery to view student artwork.',
                'date_posted' => '2025-02-22 14:00:00',
                'image_url' => 'https://picsum.photos/seed/artexhibition/800/600',
                'user_like' => 0,
                'likes' => 18,
                'comments' => [
                    [
                        'user_id' => 9,
                        'name' => 'Grace Hopper',
                        'comment' => 'The art pieces are stunning.',
                        'date' => '2025-02-22 16:30:00',
                        'profile_url' => 'https://example.com/profiles/9'
                    ]
                ]
            ],
            [
                'id' => 7,
                'title' => 'Science fair winners announced',
                'content' => 'The winners of the annual science fair have been announced. Congratulations to all participants!',
                'date_posted' => '2025-02-23 13:30:00',
                'image_url' => 'https://picsum.photos/seed/sciencefair/800/600',
                'user_like' => 1,
                'likes' => 55,
                'comments' => [
                    [
                        'user_id' => 10,
                        'name' => 'Henry Ford',
                        'comment' => 'Impressive projects this year.',
                        'date' => '2025-02-23 15:00:00',
                        'profile_url' => 'https://example.com/profiles/10'
                    ],
                    [
                        'user_id' => 11,
                        'name' => 'Ivy League',
                        'comment' => 'Well deserved awards!',
                        'date' => '2025-02-23 15:20:00',
                        'profile_url' => 'https://example.com/profiles/11'
                    ]
                ]
            ],
            [
                'id' => 8,
                'title' => 'Music concert in the auditorium',
                'content' => 'The school will host a music concert in the auditorium this Friday evening. Enjoy the performances.',
                'date_posted' => '2025-02-24 17:00:00',
                'image_url' => 'https://picsum.photos/seed/musicconcert/800/600',
                'user_like' => 0,
                'likes' => 30,
                'comments' => [
                    [
                        'user_id' => 12,
                        'name' => 'Jack Sparrow',
                        'comment' => "Can't wait to hear the bands.",
                        'date' => '2025-02-24 18:15:00',
                        'profile_url' => 'https://example.com/profiles/12'
                    ]
                ]
            ],
            [
                'id' => 9,
                'title' => 'New cafeteria menu introduced',
                'content' => 'The school cafeteria has a new menu with healthier options. Students can check out the updated list.',
                'date_posted' => '2025-02-25 12:30:00',
                'image_url' => 'https://picsum.photos/seed/cafeteriamenu/800/600',
                'user_like' => 1,
                'likes' => 22,
                'comments' => [
                    [
                        'user_id' => 13,
                        'name' => 'Karen Page',
                        'comment' => 'The salads look great!',
                        'date' => '2025-02-25 13:45:00',
                        'profile_url' => 'https://example.com/profiles/13'
                    ]
                ]
            ],
            [
                'id' => 10,
                'title' => 'Guest lecture on technology',
                'content' => 'A renowned guest speaker will deliver a lecture on emerging technologies next week. Check the schedule.',
                'date_posted' => '2025-02-26 10:15:00',
                'image_url' => 'https://picsum.photos/seed/guestlecture/800/600',
                'user_like' => 0,
                'likes' => 33,
                'comments' => [
                    [
                        'user_id' => 14,
                        'name' => 'Leonardo DiCaprio',
                        'comment' => 'This is going to be informative.',
                        'date' => '2025-02-26 11:30:00',
                        'profile_url' => 'https://example.com/profiles/14'
                    ]
                ]
            ],
            [
                'id' => 11,
                'title' => 'Library extended hours',
                'content' => 'The school library will now be open until 8 PM on weekdays to support student studies.',
                'date_posted' => '2025-02-27 08:00:00',
                'image_url' => 'https://picsum.photos/seed/libraryextended/800/600',
                'user_like' => 1,
                'likes' => 28,
                'comments' => [
                    [
                        'user_id' => 15,
                        'name' => 'Maria Gonzalez',
                        'comment' => 'Great for late-night studies.',
                        'date' => '2025-02-27 09:20:00',
                        'profile_url' => 'https://example.com/profiles/15'
                    ]
                ]
            ],
            [
                'id' => 12,
                'title' => 'Drama club performance scheduled',
                'content' => 'The drama club will perform a classic play this weekend. Tickets are available at the front desk.',
                'date_posted' => '2025-02-28 14:45:00',
                'image_url' => 'https://picsum.photos/seed/dramaclub/800/600',
                'user_like' => 0,
                'likes' => 40,
                'comments' => [
                    [
                        'user_id' => 16,
                        'name' => 'Nancy Drew',
                        'comment' => 'Looking forward to a great performance.',
                        'date' => '2025-02-28 16:00:00',
                        'profile_url' => 'https://example.com/profiles/16'
                    ],
                    [
                        'user_id' => 17,
                        'name' => 'Oliver Twist',
                        'comment' => 'Will there be a Q&A session?',
                        'date' => '2025-02-28 16:30:00',
                        'profile_url' => 'https://example.com/profiles/17'
                    ]
                ]
            ],
            [
                'id' => 13,
                'title' => 'Math club competition results',
                'content' => 'The results from the inter-school math competition have been released. Congratulations to our winners!',
                'date_posted' => '2025-03-01 11:00:00',
                'image_url' => 'https://picsum.photos/seed/mathcompetition/800/600',
                'user_like' => 1,
                'likes' => 60,
                'comments' => [
                    [
                        'user_id' => 18,
                        'name' => 'Peter Parker',
                        'comment' => 'Proud of our math team!',
                        'date' => '2025-03-01 12:15:00',
                        'profile_url' => 'https://example.com/profiles/18'
                    ]
                ]
            ],
            [
                'id' => 14,
                'title' => 'Environmental awareness campaign',
                'content' => 'The school is launching an environmental awareness campaign. Join us for a tree planting event this Saturday.',
                'date_posted' => '2025-03-02 09:30:00',
                'image_url' => 'https://picsum.photos/seed/environment/800/600',
                'user_like' => 0,
                'likes' => 18,
                'comments' => [
                    [
                        'user_id' => 19,
                        'name' => 'Quentin Tarantino',
                        'comment' => 'Great initiative!',
                        'date' => '2025-03-02 10:45:00',
                        'profile_url' => 'https://example.com/profiles/19'
                    ]
                ]
            ]
        ];
    }
}
