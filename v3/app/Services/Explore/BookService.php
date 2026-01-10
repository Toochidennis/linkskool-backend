<?php

namespace V3\App\Services\Explore;

class BookService
{
    public function __construct()
    {
    }

    public function getList(): array
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
