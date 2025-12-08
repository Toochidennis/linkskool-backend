<?php

namespace V3\App\Common\DTO;

class ValidationError
{
    public int $year;
    public int $questionIndex;
    public string $questionText;
    public string $error;
}
