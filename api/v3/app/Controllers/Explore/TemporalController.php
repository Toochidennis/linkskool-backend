<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Routing\{Route, Group};

#[Group('/public')]
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
                        "video_url" => "https://youtu.be/bIjCOHLbdLQ?si=s9R-xAGOk4yHLQV1",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["J", "F", " "],
                        ],
                    ],
                    [
                        "title" => "U, R, and K Keys",
                        "video_url" => "https://youtu.be/WwjpmB3JaD0?si=XoSel3R_ScE1K0ja",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["U", "R", "K"],
                        ],
                    ],
                    [
                        "title" => "D, E, and I Keys",
                        "video_url" => "https://youtu.be/MKUZ7pwuWnI?si=0aSSCCKqcjUdyDqU",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["D", "E", "I"],
                        ],
                    ],
                    [
                        "title" => "C, G, and N Keys",
                        "video_url" => "https://youtu.be/sQdP0fNLPKI?si=_v-rlD7_kAyMA5NT",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["C", "G", "N"],
                        ],
                    ],
                    [
                        "title" => "T, S, and L Keys",
                        "video_url" => "https://youtu.be/dofs21zXPV8?si=asDi6sAA1o8zj69j",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["T", "S", "L"],
                        ],
                    ],
                    [
                        "title" => "O, B, and A Keys",
                        "video_url" => "https://youtu.be/8myzaNT0zHc?si=j8JbmSv4AtqYy6xD",
                        "test" => [
                            "type" => "typing",
                            "letters" => ["O", "B", "A"],
                        ],
                    ],
                    [
                        "title" => "V, H, and M Keys",
                        "video_url" => "https://youtu.be/mgmg83A74ik?si=oCuMsayMSiiiIEZC",
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
                        "video_url" => "https://youtu.be/0VOO2JW6iEM?si=NhD7Mfll7ugebdHa",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "She walks to school every morning.",
                                "They enjoy playing games together.",
                                "I need some water and a sandwich.",
                                "The dog barked at the stranger.",
                                "Please close the window before you leave."
                            ]
                        ],
                    ],
                    [
                        "title" => "Easy Home Row Words",
                        "video_url" => "https://youtu.be/9NAUMb3RV64?si=JLpg7DeGjVcDE-Ln",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Dad had a salad for lunch.",
                                "Sally adds salt and ham.",
                                "Glad lads had a dash.",
                                "Hal had a fast hand.",
                                "A sad lad had a bad fall."
                            ]
                        ],
                    ],
                    [
                        "title" => "Easy Top Row Words",
                        "video_url" => "https://youtu.be/P3Ox9eq0JeY?si=f-NEnZNVVDzE-z7I",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "We were out to type proper text.",
                                "Try to wipe your white pipe.",
                                "Peter wrote quite a weird quote.",
                                "You type with pure power.",
                                "I owe you two pretty pens."
                            ]
                        ],
                    ],
                    [
                        "title" => "Easy Bottom Row Words",
                        "video_url" => "https://youtu.be/mApWmqzWifk?si=jZjOJf1a1g70ll5I",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Max can move boxes now.",
                                "Zoe baked a banana muffin.",
                                "Mix jam and cocoa in a big cup.",
                                "Buzz and Ben ran back.",
                                "Kim put a box next to me."
                            ]
                        ],
                    ],
                    [
                        "title" => "Shift Key and Capitalization",
                        "video_url" => "https://youtu.be/jKk7T4vlNLk?si=tFEOKgzvBZ9IhChX",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Alice Went To Paris In April.",
                                "My Brother's Name Is Daniel.",
                                "Yesterday, We Saw A Big Elephant.",
                                "Can You Help Me With This Book?",
                                "Grandma Bakes The Best Cookies!"
                            ]
                        ],
                    ],
                    [
                        "title" => "Basic Punctuation",
                        "video_url" => "https://youtu.be/yJ-nJsl3kEk?si=iZFhFimOJjE-Ul6j",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Hello, my friend. How are you?",
                                "Please, come in and sit down.",
                                "It's time to eat, isn't it?",
                                "Wait! Don’t go yet.",
                                "Yes, I saw the movie. It was good!"
                            ]
                        ],
                    ],
                    [
                        "title" => "Intermediate Punctuation",
                        "video_url" => "https://youtu.be/V3Jfp0aQ7g0?si=aU4IGnxcVwSjHlsf",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "She said, \"Let's meet at 5 p.m., okay?\"",
                                "James's laptop, however, wasn't working.",
                                "They bought apples, bananas, and oranges.",
                                "My mom asked, \"Where have you been?\"",
                                "Wait—did you just say, \"I'm leaving\"?"
                            ]
                        ],
                    ],
                    [
                        "title" => "Quick Sentences",
                        "video_url" => "https://youtu.be/-o5VxVPMDBk?si=i_zeal_36MvlsCEt",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "I am happy.",
                                "You are fast.",
                                "This is fun.",
                                "We love to type.",
                                "Time goes by.",
                                "She can cook.",
                                "He is kind.",
                                "They will win.",
                                "It looks good.",
                                "Let’s start now."
                            ]
                        ],
                    ],
                    [
                        "title" => "Short Paragraphs",
                        "video_url" => "https://youtu.be/nJ8scAn5k2I?si=Oqq2IEp8PcbMyKTu",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Typing every day helps you get better. The more you practice, the faster and more accurate you become.",
                                "My name is Sarah. I have a cat named Milo. He loves to play with string and sleep on my bed.",
                                "Today is sunny and bright. I went outside to walk and feel the fresh air. It was a great way to start the day.",
                                "We had rice and chicken for dinner. It tasted really good, and we all ate together at the table.",
                                "Learning to type is fun. At first, it may seem hard, but with time and effort, it becomes easy."
                            ]
                        ],
                    ],
                    [
                        "title" => "Speed Drills",
                        "video_url" => "https://youtu.be/3WYm4HLNxhk?si=MMEqJOjvi7MFWTie0",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "The cat ran fast. The cat ran fast.",
                                "Jump and run. Jump and run. Jump and run.",
                                "She is here. She is here. She is here.",
                                "Now is the time. Now is the time. Now is the time.",
                                "Fast fingers fly. Fast fingers fly. Fast fingers fly.",
                                "I can type well. I can type well. I can type well.",
                                "Go go go go go go go go go!",
                                "Run run run, stop stop stop!",
                                "Type it right. Type it right. Type it right.",
                                "Keep going! Keep going! Keep going!"
                            ]
                        ],
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
                        "video_url" => "https://youtu.be/c5oGdCXN3-o?si=XadKrWkToVEfHodp",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Typing is an essential skill in today’s digital world, and the best way to improve it is through consistent, focused practice. Whether you're writing an email, drafting a school assignment, or coding a project, fast and accurate typing can save you time and boost your productivity. The key is to start slowly, focusing on proper finger placement and accuracy, rather than speed. As your muscle memory develops, your fingers will begin to glide effortlessly across the keyboard. Don’t get discouraged by mistakes—instead, treat them as stepping stones toward mastery. Over time, you'll notice that typing becomes second nature, allowing you to express your thoughts more clearly and efficiently."
                            ]
                        ],
                    ],
                    [
                        "title" => "Numbers Letters Numbers",
                        "video_url" => "https://youtu.be/QBe2O3zP3P8?si=8hUGqixUoW2xhtc4",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "John has 3 cats and 2 dogs, all of which he feeds twice a day. On Monday, he bought 5 new bowls, 7 cans of food, and 12 treats from PetMart. His address is 42 Elm Street, and his phone number is 0803-456-7890. He wakes up by 6:30 a.m., gets ready by 7:15, and starts feeding the pets at exactly 7:30. At work, he types reports, enters figures like 1,250 and 3.75, and checks over 20 emails before lunch. Numbers are a big part of his daily routine."
                            ]
                        ],
                    ],
                    [
                        "title" => "Accuracy Focus",
                        "video_url" => "https://youtu.be/a9jx7Ziwt_k?si=5PriF_BCeBhLA2eP",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Typing quickly is impressive, but typing accurately is far more valuable. When you're in a hurry, it's easy to make small mistakes that can change the meaning of your sentence entirely. For example, mixing up 'their' and 'there' or missing a single punctuation mark can confuse readers. That's why it's important to slow down, proofread, and aim for precision. The goal is not just speed, but clean, clear writing that communicates exactly what you intend. Focus on each word, and your accuracy will improve over time."
                            ]
                        ],
                    ],
                    [
                        "title" => "Advanced Symbols",
                        "video_url" => "https://youtu.be/kMyvWPLMEJM?si=ZmTApDk-VnZxvA25",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "To create a secure password, use a mix of letters, numbers, and symbols like @, #, %, &, and *. For example, a strong password might look like P@ssw0rd#2025! Always avoid using simple combinations such as 123456 or your name and birthday. When coding, you'll often need to use symbols such as (), {}, [], and even $ or ; to define logic, structure, and behavior. In social media, hashtags (#) help categorize content, while the @ symbol is used to mention or tag other users. Becoming comfortable with these symbols improves both your digital communication and technical skills."
                            ]
                        ],
                    ],
                    [
                        "title" => "Numeric Keypad",
                        "video_url" => "https://youtu.be/m-Y3YqVo3rY?si=_pajXgN36NirbYlI",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "The numeric keypad is essential for quickly entering numbers, especially in tasks like accounting, data entry, and calculations. For example, a cashier might enter prices like 149.99, 75.50, and 20.00 throughout the day. In spreadsheets, numbers such as 1,250; 3.75; and 9,843.12 are common. Using the keypad for inputting phone numbers (e.g., 0803-456-7890), product codes (e.g., 4021, 1583, 9982), and dates like 07/09/2025 can significantly boost typing speed and accuracy. Practicing regularly on the keypad helps build finger memory and improves overall efficiency with numeric data."
                            ]
                        ],
                    ],
                    [
                        "title" => "Advanced Wrap-up",
                        "video_url" => "https://youtu.be/RYjPLK2uGoo?si=8UL_JoJIUNcZZZSC",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "Congratulations on reaching the final stage! By now, you’ve typed hundreds of words, mastered symbols like @, %, and $, and grown more confident with punctuation, numbers, and capital letters. You’ve typed sentences such as “She earned $1,250 in just 2 weeks!” and handled tricky structures like: Wait—did he really say, “I’ll do it by 5:00 p.m.”? As you continue to practice, focus on combining speed and accuracy. Whether you're drafting emails, writing reports, or entering data, your skills will set you apart. Keep practicing, stay consistent, and remember: precision beats speed every time."
                            ]
                        ],
                    ],
                    [
                        "title" => "Advanced Assessment",
                        "video_url" => "https://youtu.be/h1RboD6GbnU?si=rUz87-ZK5PEk6pIz",
                        "test" => [
                            "type" => "typing",
                            "letters" => [
                                "During the final assessment, you'll need to demonstrate everything you've learned: speed, accuracy, and control. Type numbers like 3,280.50 and dates such as 07/09/2025 correctly, use symbols like %, @, and &, and apply punctuation in sentences like, “Wait, didn’t he say ‘Submit by 4:45 p.m.’?” Ensure proper capitalization in names like Dr. Allen, Mr. Rivera, and cities like New York or Lagos. Whether you're filling out forms, writing emails, or preparing reports, this test will show how well you’ve built your typing skillset. Stay calm, focus, and let your fingers do the work."
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ];

    #[Route('/key-buddy/content', 'GET', [])]
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
