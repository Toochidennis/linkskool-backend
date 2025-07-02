<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\ResponseHandler;

class TemporalController
{
    private array $content = [
        "levels" => [
            [
                "name" => "Beginner",
                "paid" => true,
                "unlocked_Count" => 3,
                "lessons" => [
                    [
                        "title" => "J, F, and Space",
                        "video_Url" => "https://youtu.be/bIjCOHLbdLQ?si=s9R-xAGOk4yHLQV1",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["J", "F", " "],
                        ],
                    ],
                    [
                        "title" => "U, R, and K Keys",
                        "video_Url" => "https://youtu.be/WwjpmB3JaD0?si=XoSel3R_ScE1K0ja",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["U", "R", "K"],
                        ],
                    ],
                    [
                        "title" => "D, E, and I Keys",
                        "video_Url" => "https://youtu.be/MKUZ7pwuWnI?si=0aSSCCKqcjUdyDqU",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["D", "E", "I"],
                        ],
                    ],
                    [
                        "title" => "C, G, and N Keys",
                        "video_Url" => "https://youtu.be/sQdP0fNLPKI?si=_v-rlD7_kAyMA5NT",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["C", "G", "N"],
                        ],
                    ],
                    [
                        "title" => "T, S, and L Keys",
                        "video_Url" => "https://youtu.be/dofs21zXPV8?si=asDi6sAA1o8zj69j",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["T", "S", "L"],
                        ],
                    ],
                    [
                        "title" => "O, B, and A Keys",
                        "video_Url" => "https://youtu.be/8myzaNT0zHc?si=j8JbmSv4AtqYy6xD",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["O", "B", "A"],
                        ],
                    ],
                    [
                        "title" => "V, H, and M Keys",
                        "video_Url" => "https://youtu.be/mgmg83A74ik?si=oCuMsayMSiiiIEZC",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["V", "H", "M"],
                        ],
                    ],
                ],
            ],
            [
                "name" => "Intermediate",
                "paid" => true,
                "unlocked_Count" => 3,
                "lessons" => [
                    [
                        "title" => "Common English Words",
                        "video_Url" => "https://youtu.be/0VOO2JW6iEM?si=NhD7Mfll7ugebdHa",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Easy Home Row Words",
                        "video_Url" => "https://youtu.be/9NAUMb3RV64?si=JLpg7DeGjVcDE-Ln",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Easy Top Row Words",
                        "video_Url" => "https://youtu.be/P3Ox9eq0JeY?si=f-NEnZNVVDzE-z7I",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Easy Bottom Row Words",
                        "video_Url" => "https://youtu.be/mApWmqzWifk?si=jZjOJf1a1g70ll5I",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Shift Key and Capitalization",
                        "video_Url" => "https://youtu.be/jKk7T4vlNLk?si=tFEOKgzvBZ9IhChX",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Basic Punctuation",
                        "video_Url" => "https://youtu.be/yJ-nJsl3kEk?si=iZFhFimOJjE-Ul6j",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Intermediate Punctuation",
                        "video_Url" => "https://youtu.be/V3Jfp0aQ7g0?si=aU4IGnxcVwSjHlsf",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Quick Sentences",
                        "video_Url" => "https://youtu.be/-o5VxVPMDBk?si=i_zeal_36MvlsCEt",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Short Paragraphs",
                        "video_Url" => "https://youtu.be/nJ8scAn5k2I?si=Oqq2IEp8PcbMyKTu",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Speed Drills",
                        "video_Url" => "https://youtu.be/3WYm4HLNxhk?si=MMEqJOjvi7MFWTie0",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                ],
            ],
            [
                "name" => "Advanced",
                "paid" => true,
                "unlocked_Count" => 3,
                "lessons" => [
                    [
                        "title" => "Skill Builder",
                        "video_Url" => "https://youtu.be/c5oGdCXN3-o?si=XadKrWkToVEfHodp",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Numbers Letters Numbers",
                        "video_Url" => "https://youtu.be/QBe2O3zP3P8?si=8hUGqixUoW2xhtc4",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Accuracy Focus",
                        "video_Url" => "https://youtu.be/a9jx7Ziwt_k?si=5PriF_BCeBhLA2eP",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Advanced Symbols",
                        "video_Url" => "https://youtu.be/kMyvWPLMEJM?si=ZmTApDk-VnZxvA25",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Numeric Keypad",
                        "video_Url" => "https://youtu.be/m-Y3YqVo3rY?si=_pajXgN36NirbYlI",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Advanced Wrap-up",
                        "video_Url" => "https://youtu.be/RYjPLK2uGoo?si=8UL_JoJIUNcZZZSC",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                    [
                        "title" => "Advanced Assessment",
                        "video_Url" => "https://youtu.be/h1RboD6GbnU?si=rUz87-ZK5PEk6pIz",
                        "test" => ["type" => "typing", "letters" => []],
                    ],
                ],
            ],
        ],
    ];

    public function getContent()
    {
        ResponseHandler::sendJsonResponse(
            [
                'success' => true,
                'response' => $this->content
            ]
        );
    }
}
