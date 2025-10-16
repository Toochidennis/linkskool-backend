<?php

namespace V3\App\Services\Explore;

class GameService
{
    public function __construct()
    {
    }

    public function getList(): array
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
}
