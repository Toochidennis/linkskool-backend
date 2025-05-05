<?php

namespace V3\App\Services\Explore;

class MovieService
{
    public function movies()
    {
        return [
            'hot' => [
                [
                    'title' => 'The Mark of Zorro 1940 colorized (Tyrone Power)',
                    'thumbnail' => 'https://archive.org/download/mark-of-zorro-1940/mark-of-zorro-1940.thumbs/Mark%20of%20Zorro%201940_000717.jpg',
                    'description' => 'A young Spanish aristocrat must masquerade as a fop in order to maintain his secret identity of Zorro as he restores justice to early California',
                    'category' => 'Horror',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://dn720006.ca.archive.org/0/items/mark-of-zorro-1940/Mark%20of%20Zorro%201940.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'D.O.A. (1949)',
                    'thumbnail' => 'https://archive.org/download/DOA1949/DOA1949_thumb.jpg',
                    'description' => 'A poisoned man investigates his own murder.',
                    'category' => 'Crime',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/DOA1949/DOA1949_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'Ocean Waves',
                    'thumbnail' => 'https://images.pexels.com/photos/5531150/pexels-photo-5531150.jpeg?cs=srgb&dl=pexels-ericcapilador-5531150.jpg&fm=jpg&_gl=1*1r2hktr*_ga*NTA1MTM4MjM0LjE3NDY0NTIxNjc.*_ga_8JE65Q40S6*MTc0NjQ1MjE2Ny4xLjEuMTc0NjQ1MzE3OC4wLjAuMA..',
                    'description' => 'Relaxing ocean wave footage.',
                    'category' => 'Nature',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://www.pexels.com/download/video/1918465/'
                        ]
                    ]
                ],
                [
                    'title' => 'Sherlock Holmes (1922)',
                    'thumbnail' => 'https://archive.org/download/SherlockHolmes1922/SherlockHolmes_thumb.jpg',
                    'description' => 'Classic detective story of Sherlock Holmes.',
                    'category' => 'Mystery',
                    'hasSubSeries' => true,
                    'episodes' => [
                        [
                            'title' => 'Episode 1',
                            'videoUrl' => 'https://archive.org/download/SherlockHolmes1922/SherlockHolmes_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'Mountain Aerial',
                    'thumbnail' => 'https://images.pexels.com/videos/856192/pexels-photo-856192.jpeg',
                    'description' => 'Aerial view of snowy mountain range.',
                    'category' => 'Scenic',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238297648.sd.mp4?s=f8f2f48d0e47d1edbf29c12f84f4c07e&profile_id=164'
                        ]
                    ]
                ]
            ],

            'recommended' => [
                [
                    'title' => 'His Girl Friday',
                    'thumbnail' => 'https://archive.org/download/HisGirlFriday/HisGirlFriday_thumb.jpg',
                    'description' => 'A fast-talking newspaper editor tries to win back his ex-wife.',
                    'category' => 'Comedy',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/HisGirlFriday/HisGirlFriday_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'City Timelapse',
                    'thumbnail' => 'https://images.pexels.com/videos/854162/pexels-photo-854162.jpeg',
                    'description' => 'Timelapse of a busy urban road.',
                    'category' => 'Urban',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/240828694.sd.mp4?s=15329b6e58c5e58b3f6c9dbec79b9be6&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'The Killer Shrews',
                    'thumbnail' => 'https://archive.org/download/KillerShrews/KillerShrews_thumb.jpg',
                    'description' => 'Scientists face off against mutant shrews on a remote island.',
                    'category' => 'Sci-Fi',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/KillerShrews/KillerShrews_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'The Brain That Wouldn’t Die',
                    'thumbnail' => 'https://archive.org/download/TheBrainThatWouldntDie/TheBrainThatWouldntDie_thumb.jpg',
                    'description' => 'A doctor keeps his girlfriend’s head alive in a pan.',
                    'category' => 'Horror',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/TheBrainThatWouldntDie/TheBrainThatWouldntDie_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'Snowy Trees',
                    'thumbnail' => 'https://images.pexels.com/videos/856966/pexels-photo-856966.jpeg',
                    'description' => 'Snow falls gently in a winter forest.',
                    'category' => 'Nature',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238298432.sd.mp4?s=1f4cf06e36ed025848317c87b39e4355&profile_id=164'
                        ]
                    ]
                ]
            ],

            'youMayAlsoLike' => [
                [
                    'title' => 'Forest Waterfall',
                    'thumbnail' => 'https://images.pexels.com/videos/857203/pexels-photo-857203.jpeg',
                    'description' => 'A peaceful waterfall in a dense forest.',
                    'category' => 'Nature',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238295342.sd.mp4?s=f320f44ce7a3219cb4b3d3dd0ee0c45b&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'The Last Man on Earth',
                    'thumbnail' => 'https://archive.org/download/TheLastManOnEarth/TheLastManOnEarth_thumb.jpg',
                    'description' => 'A scientist fights off vampire-like beings after a plague.',
                    'category' => 'Horror',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/TheLastManOnEarth/TheLastManOnEarth_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'Calm River Stream',
                    'thumbnail' => 'https://images.pexels.com/videos/856971/pexels-photo-856971.jpeg',
                    'description' => 'A calm stream flows through the countryside.',
                    'category' => 'Nature',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238298864.sd.mp4?s=d180cf2070cc59e0c4abf1ce228e6c26&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'Topper Returns (1941)',
                    'thumbnail' => 'https://archive.org/download/TopperReturns/TopperReturns_thumb.jpg',
                    'description' => 'A ghost tries to solve her own murder.',
                    'category' => 'Comedy',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/TopperReturns/TopperReturns_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'Aerial Landscape',
                    'thumbnail' => 'https://images.pexels.com/videos/856987/pexels-photo-856987.jpeg',
                    'description' => 'Drone footage over lush green fields.',
                    'category' => 'Scenic',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238299230.sd.mp4?s=64b0c8c3b29b1f5a33cfeb3a6b329329&profile_id=164'
                        ]
                    ]
                ]
            ],

            'othersAvailable' => [
                [
                    'title' => 'Night Sky Stars',
                    'thumbnail' => 'https://images.pexels.com/videos/854267/pexels-photo-854267.jpeg',
                    'description' => 'Stars move across a clear night sky.',
                    'category' => 'Astronomy',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/240832303.sd.mp4?s=29f234705c5df90b2cb7b780899428a7&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'The Phantom of the Opera (1925)',
                    'thumbnail' => 'https://archive.org/download/PhantomOfTheOpera1925/PhantomOfTheOpera_thumb.jpg',
                    'description' => 'A deformed phantom haunts the Paris Opera House.',
                    'category' => 'Horror',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/PhantomOfTheOpera1925/PhantomOfTheOpera_512kb.mp4'
                        ]
                    ]
                ],
                [
                    'title' => 'City Sunrise',
                    'thumbnail' => 'https://images.pexels.com/videos/857183/pexels-photo-857183.jpeg',
                    'description' => 'Sunrise over a peaceful city skyline.',
                    'category' => 'Urban',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/257160982.sd.mp4?s=00eb6aa099146a89eb44efbe98b5d273&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'Running River',
                    'thumbnail' => 'https://images.pexels.com/videos/856994/pexels-photo-856994.jpeg',
                    'description' => 'A fast-moving river cutting through a valley.',
                    'category' => 'Nature',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Clip',
                            'videoUrl' => 'https://player.vimeo.com/external/238299437.sd.mp4?s=ec8130c04f9c246ab51d7d8792e91e8a&profile_id=164'
                        ]
                    ]
                ],
                [
                    'title' => 'The Little Shop of Horrors (1960)',
                    'thumbnail' => 'https://archive.org/download/LittleShopOfHorrors/LittleShopOfHorrors_thumb.jpg',
                    'description' => 'A man grows a plant that feeds on blood.',
                    'category' => 'Comedy/Horror',
                    'hasSubSeries' => false,
                    'episodes' => [
                        [
                            'title' => 'Full Movie',
                            'videoUrl' => 'https://archive.org/download/LittleShopOfHorrors/LittleShopOfHorrors_512kb.mp4'
                        ]
                    ]
                ]
            ]
        ];
    }
}
