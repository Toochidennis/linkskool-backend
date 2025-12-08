<?php

namespace V3\App\Common\DTO;

class OptionItem
{
    public int $order;
    public string $text;
    /** @var QuestionFile[] */
    public array $optionFiles = [];
}
