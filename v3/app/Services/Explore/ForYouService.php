<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Category;
use V3\App\Models\Explore\Video;
use V3\App\Models\Portal\Academics\Course;

class ForYouService
{
    private Category $category;
    private Video $video;
    private Course $course;

    public function __construct(\PDO $pdo)
    {
        $this->category = new Category($pdo);
        $this->course = new Course($pdo);
        $this->video = new Video($pdo);
    }

    public function getList()
    {
        return [
            'games' => $this->getGames(),
            'books' => $this->getBooks(),
            'videos' => $this->getVideos()
        ];
    }

    private function getGames()
    {
        return [
            "Card Games" => [
                "id"    => "1",
                "name"  => "Card Games",
                "games" => [
                    [
                        "id"          => "173",
                        "gameUrl"     => "https://games.softgames.com/games/daily-solitaire-2020/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/daily-solitaire-2020/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "5",
                        "date"        => "March 28, 2020",
                        "title"       => "Daily Solitaire 2020",
                        "description" => "Enjoy a modern twist on the classic solitaire game with updated graphics and features."
                    ],
                    [
                        "id"          => "175",
                        "gameUrl"     => "https://games.softgames.com/games/blackjack-classic/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/blackjack-classic/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "4",
                        "date"        => "April 15, 2020",
                        "title"       => "Blackjack Classic",
                        "description" => "Test your skills in Blackjack and beat the dealer in this classic card game."
                    ]
                ]
            ],
            "Puzzle Games" => [
                "id"    => "2",
                "name"  => "Puzzle Games",
                "games" => [
                    [
                        "id"          => "174",
                        "gameUrl"     => "https://games.softgames.com/games/bubble-shooter-free/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/bubble-shooter-free/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "5",
                        "date"        => "March 28, 2020",
                        "title"       => "Bubble Shooter FREE",
                        "description" => "Aim and shoot bubbles to create matches and clear the board in this addicting puzzle game."
                    ],
                    [
                        "id"          => "176",
                        "gameUrl"     => "https://games.softgames.com/games/jungle-match/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/jungle-match/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "4",
                        "date"        => "April 1, 2020",
                        "title"       => "Jungle Match",
                        "description" => "Match colorful jungle-themed pieces to solve puzzles and advance through levels."
                    ]
                ]
            ],
            "Board Games" => [
                "id"    => "3",
                "name"  => "Board Games",
                "games" => [
                    [
                        "id"          => "177",
                        "gameUrl"     => "https://games.softgames.com/games/chess-battle/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/chess-battle/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "5",
                        "date"        => "April 5, 2020",
                        "title"       => "Chess Battle",
                        "description" => "Engage in strategic battles on the chessboard with enhanced graphics and smart AI opponents."
                    ]
                ]
            ],
            "Action Games" => [
                "id"    => "4",
                "name"  => "Action Games",
                "games" => [
                    [
                        "id"          => "178",
                        "gameUrl"     => "https://games.softgames.com/games/space-invaders/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/space-invaders/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "4",
                        "date"        => "April 10, 2020",
                        "title"       => "Space Invaders",
                        "description" => "Defend your base from waves of alien invaders in this retro-style action game."
                    ],
                    [
                        "id"          => "179",
                        "gameUrl"     => "https://games.softgames.com/games/warzone-fury/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/warzone-fury/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "4",
                        "date"        => "April 12, 2020",
                        "title"       => "Warzone Fury",
                        "description" => "Experience intense combat and strategic gameplay in this thrilling action game."
                    ]
                ]
            ],
            "Sports Games" => [
                "id"    => "5",
                "name"  => "Sports Games",
                "games" => [
                    [
                        "id"          => "180",
                        "gameUrl"     => "https://games.softgames.com/games/soccer-champions/gamesites/6130/",
                        "thumbnail"   => "https://d1bjj4kazoovdg.cloudfront.net/assets/games/soccer-champions/big_icon.jpg?p=pub-13616-13892",
                        "rating"      => "5",
                        "date"        => "April 15, 2020",
                        "title"       => "Soccer Champions",
                        "description" => "Take control of your team and compete in exciting soccer tournaments with realistic gameplay."
                    ]
                ]
            ]
        ];
    }

    private function getVideos(): array
    {
        $videos = $this->video
            ->select([
                'videosTable.id',
                'videosTable.videoTitle as title',
                'videosTable.thumbnail',
                'videosTable.course_id',
                'videosTable.categoryId',
                'videosTable.videoUrl as url',
                'level.name as level_name',
                'level.id AS level_id',
            ])
            ->join('level', 'level.id = videosTable.level')
            ->where('videoUrl', '<>', '')
            ->get();

        if (!$videos) {
            return [];
        }

        // Load reference data
        $courses   = $this->loadCourse();
        $categories = $this->loadCategory();

        // Map data for quick lookup
        $courseMap = [];
        foreach ($courses as $c) {
            $courseMap[$c['id']] = $c['course_name'];
        }

        $categoryMap = [];
        foreach ($categories as $c) {
            $categoryMap[$c['id']] = [
                'category_name' => $c['category_name'],
            ];
        }

        $arr = [];
        $vtr = [];

        // Rebuild array structure
        foreach ($videos as $row) {
            $courseId  = $row['course_id'];
            $categoryId = $row['categoryId'];

            // Set up course block
            if (!isset($arr[$courseId])) {
                $arr[$courseId] = [
                    'id' => $courseId,
                    'name' => $courseMap[$courseId] ?? 'Unknown Course',
                    'category' => [],
                ];
            }

            // Set up category block
            if (!isset($vtr[$courseId][$categoryId])) {
                $vtr[$courseId][$categoryId] = [
                    'id' => $categoryId,
                    'level' => $row['level_id'],
                    'level_name' => $row['level_name'],
                    'name' => $categoryMap[$categoryId]['category_name'] ?? 'Unknown Category',
                    'videos' => [],
                ];
            }

            // Format YouTube embed URL
            $videoUrl = "https://www.youtube.com/embed/" . $row['url'] . "?modestbranding=0&showinfo=0";

            // Append video to category
            $vtr[$courseId][$categoryId]['videos'][] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'url' => $videoUrl,
                'thumbnail' => $row['thumbnail'],
            ];

            // Attach to course
            $arr[$courseId]['category'] = $vtr[$courseId];
        }

        // Sort alphabetically by course name and category name
        usort($arr, fn($a, $b) => strcmp($a['name'], $b['name']));
        foreach ($arr as &$course) {
            $categories = array_values($course['category']);
            usort($categories, fn($a, $b) => strcmp($a['name'], $b['name']));
            $course['category'] = $categories;
        }

        return array_values($arr);
    }

    /**
     * Fetch list of available courses.
     */
    private function loadCourse(): array
    {
        return $this->course
            ->select(['id', 'course_name'])
            ->orderBy('course_name')
            ->get();
    }

    /**
     * Fetch list of video categories by course.
     */
    private function loadCategory(): array
    {
        return $this->category
            ->select(['id', 'categoryName AS category_name'])
            ->orderBy('categoryName')
            ->get();
    }

    private function getBooks()
    {
        return [
            "categories" => [
                "fantasy",
                "adventure",
                "fiction",
                "philosophy",
                "magical realism",
                "steampunk",
                "mystery",
                "horror",
                "sci_fi",
                "romance",
                "drama",
                "historical_fiction"
            ],
            "books" => [
                [
                    "id" => 0,
                    "title" => "Sugar Girl",
                    "author" => "UBE Reader Boosters",
                    "thumbnail" => "https://picsum.photos/200/300?random=1",
                    "introduction" => "In a crumbling bakery at the edge of town, twelve year old Lily discovers a hidden recipe book that transports her to the Saccharine Realm, a dying world of sentient desserts. To save both her family's bakery and this sugary dimension from the Grey Mold, she must master forgotten culinary magic while confronting her fear of failure. But when her creations start manifesting dangerous sentience, Lily learns that sweetness often hides bitter truths.",
                    "categories" => ["fantasy", "adventure", "fiction"],
                    "chapters" => [
                        "chapter_1" => "The stale croissant in Lily's hand began vibrating. Before she could scream, it sprouted cinnamon stick legs and scuttled into a wall crack. Hours later, covered in flour and disbelief, she found the rusted cake stand portal. The air smelled of burnt caramel and regret.",
                        "chapter_2" => "The Chocolate River was screaming. Waves clawed at marzipan cliffs as Lily and Gummy raced toward the Hollow Truffle mines. The Mold feeds on doubt, Gummy yelled. Lily clutched her grandmother's whisk, glowing with spectral frosting.",
                        "chapter_3" => "As the final crystal sprinkle slotted into the Croquembouche Crown, Lily realized the terrible joke of creation magic. To stabilize the realm meant binding her consciousness to its core. Years later, townsfolk whispered of the bakery whose pastries healed broken hearts."
                    ]
                ],
                [
                    "id" => 1,
                    "title" => "The Midnight Library",
                    "author" => "Nova Pagewright",
                    "thumbnail" => "https://via.placeholder.com/200x300.png/0088ff?text=Midnight+Library",
                    "introduction" => "Depressed librarian Theo stumbles into a metaphysical archive where every book represents a life choice he never made. But the library is collapsing, devoured by the Erasure, a force consuming unrealized possibilities. To save infinite versions of himself, Theo must write new endings for abandoned stories while confronting his most dangerous creation, a charismatic alternate self determined to become the true Theo.",
                    "categories" => ["fantasy", "philosophy", "fiction"],
                    "chapters" => [
                        "chapter_1" => "Theo's shadow stretched toward the exit, solidifying into a door shaped like his childhood toy. Inside, endless aisles pulsed with echoes of laughter. Welcome to your could have beens, said a version of himself without scars.",
                        "chapter_2" => "In one timeline, Theo's fingers bled on piano keys as the audience threw roses that turned to ash. This reality is starving, his alternate self hissed. Every book he touched began rewriting itself, including the one where his mother survived.",
                        "chapter_3" => "The final confrontation happened in the blank margins. Anti Theo had absorbed seventeen realities. As the library burned, Theo wrote an ending where nothing was resolved, but everything continued."
                    ]
                ],
                [
                    "id" => 2,
                    "title" => "Whispers of the Wind",
                    "author" => "Clara M Inkwell",
                    "thumbnail" => "https://source.unsplash.com/200x300/?fantasy,book",
                    "introduction" => "In a world where spoken words manifest physically, mute blacksmith Kai communicates through forged speech ingots. When his sister unleashes a forbidden syllable that births a sentient storm, Kai embarks on a journey to the Word Foundry at the edge of the Silent Sea. To recast the apocalyptic word, he must sacrifice his ability to forge meaning and confront why he truly surrendered his voice.",
                    "categories" => ["fantasy", "magical_realism", "fiction"],
                    "chapters" => [
                        "chapter_1" => "Kai hammered the word maybe into a dagger's blade. Outside, laughter crystallized into shards. When his sister sneezed, the unspeakable syllable Umbra escaped, and shadows thickened.",
                        "chapter_2" => "At the Floating Bazaar, Kai traded his childhood nickname for a corroded letter R. The merchant warned, The Foundry consumes more than metal.",
                        "chapter_3" => "The Word Foundry was alive. To recast Umbra, Kai became the crucible. As he poured his words into the core, he heard the storm whisper, I just wanted to mean something."
                    ]
                ],
                [
                    "id" => 3,
                    "title" => "The Clockwork Oracle",
                    "author" => "Dr Gideon Gear",
                    "thumbnail" => "https://loremflickr.com/200/300/steampunk",
                    "introduction" => "In 1897 London, disgraced engineer Ada and her autistic brother construct a brass automaton that predicts crimes before they happen. When the machine begins issuing prophecies in blood, they uncover a hidden war between necromancers and the sentient Babbage Engine beneath Parliament.",
                    "categories" => ["steampunk", "mystery", "fiction"],
                    "chapters" => [
                        "chapter_1" => "The automaton's first words appeared in rivets: Murder at 221B. They found Baker Street's detective dead, arranged in a Fibonacci spiral.",
                        "chapter_2" => "In the Sewer Forge, cultists chanted around a steam pentagram. Ada's blueprints mutated into scripture. Charlie spoke in machine code.",
                        "chapter_3" => "Fused with the Babbage Engine, Ada perceived time as punch cards. The automaton knelt before her, leaking oil like tears."
                    ]
                ],
                [
                    "id" => 4,
                    "title" => "Echoes of the Abyss",
                    "author" => "Mortis Nocturne",
                    "thumbnail" => "https://picsum.photos/200/300?random=4",
                    "introduction" => "A haunting tale set in a cursed village where the echoes of lost souls whisper through the fog. The villagers must confront the darkness buried within their history as spectral voices and ancient curses rise again.",
                    "categories" => ["horror", "mystery", "fantasy"],
                    "chapters" => [
                        "chapter_1" => "Whispers awaken dormant fears in the villagers.",
                        "chapter_2" => "A group unites to confront the malevolent force in the abandoned manor.",
                        "chapter_3" => "As curses resurface, they face a battle where every echo carries a warning."
                    ]
                ],
                [
                    "id" => 5,
                    "title" => "Celestial Rhapsody",
                    "author" => "Aurora Starling",
                    "thumbnail" => "https://picsum.photos/200/300?random=5",
                    "introduction" => "Across galaxies, two souls separated by time and destiny rediscover love through cosmic music. Every nebula sings their story in harmony and silence.",
                    "categories" => ["sci_fi", "romance", "philosophy"],
                    "chapters" => [
                        "chapter_1" => "Cryptic signals awaken forgotten memories of a love beyond time.",
                        "chapter_2" => "They navigate nebulae decoding melodies that bridge galaxies.",
                        "chapter_3" => "Celestial bodies align as their fates merge into eternal rhythm."
                    ]
                ],
                [
                    "id" => 6,
                    "title" => "The Silent Conductor",
                    "author" => "Evelyn Aria",
                    "thumbnail" => "https://picsum.photos/200/300?random=6",
                    "introduction" => "In a decaying opera house haunted by lost melodies, an enigmatic maestro orchestrates a performance that transcends life and death.",
                    "categories" => ["mystery", "drama", "historical_fiction"],
                    "chapters" => [
                        "chapter_1" => "The conductor's arrival awakens memories of forbidden compositions.",
                        "chapter_2" => "Spectral voices join a haunting symphony unraveling secrets of a lost era.",
                        "chapter_3" => "In a final overture, revelations merge with sorrow, sealing the opera's legacy."
                    ]
                ],
                [
                    "id" => 7,
                    "title" => "The Glass Archive",
                    "author" => "Jonas Fell",
                    "thumbnail" => "https://picsum.photos/200/300?random=7",
                    "introduction" => "A historian discovers an archive made of living glass that stores memories as reflections. When he finds his own reflection missing, he must trace which part of his past was erased and why reality is rewriting itself.",
                    "categories" => ["magical_realism", "philosophy", "mystery"],
                    "chapters" => [
                        "chapter_1" => "The mirror blinked first, showing an event that had never happened.",
                        "chapter_2" => "Each reflection was a confession written in light and time.",
                        "chapter_3" => "He stepped through the final pane, becoming history's witness and its prisoner."
                    ]
                ],
                [
                    "id" => 8,
                    "title" => "Iron Bloom",
                    "author" => "Seraphine Vale",
                    "thumbnail" => "https://picsum.photos/200/300?random=8",
                    "introduction" => "In a post collapse city overrun by plant metal hybrids, a lone botanist searches for the last true seed capable of restoring natural life.",
                    "categories" => ["sci_fi", "adventure", "fantasy"],
                    "chapters" => [
                        "chapter_1" => "Vines clanged against steel towers, blooming with iron petals.",
                        "chapter_2" => "The seed traders called it myth, but the soil remembered otherwise.",
                        "chapter_3" => "She planted hope where metal had forgotten how to breathe."
                    ]
                ]
            ]
        ];
    }
}
