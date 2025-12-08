<?php

namespace V3\App\Common\DTO;

class Question
{
    public string $questionText;
    /** @var QuestionFile[] */
    public array $questionFiles = [];
    public ?string $instruction = null;
    public ?int $instructionId = null;
    public ?string $topic = null;
    public ?int $topicId = null;
    public ?int $passageId = null;
    public ?int $explanationId = null;
    public string $questionType;
    public string $passage;
    public string $explanation;
    /** @var OptionItem[] */
    public array $options = [];
    public array $correct; // ['order'=>int, 'text'=> string]
    public ?int $year = null;
}
