<?php

namespace V3\App\Common\Enums;

enum ChallengeStatus: int
{
    case DRAFT = 0;
    case PUBLISHED = 1;
    case ARCHIVED = 2;

    /**
     * Get the enum case from an integer value
     */
    public static function fromValue(int $value): self
    {
        return match ($value) {
            self::DRAFT->value => self::DRAFT,
            self::PUBLISHED->value => self::PUBLISHED,
            self::ARCHIVED->value => self::ARCHIVED,
            default => self::DRAFT,
        };
    }

    /**
     * Get the enum case from a string label
     */
    public static function fromLabel(string $label): self
    {
        return match (strtolower($label)) {
            'draft' => self::DRAFT,
            'published' => self::PUBLISHED,
            'archived' => self::ARCHIVED,
            default => self::DRAFT,
        };
    }

    /**
     * Get the human-readable label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'draft',
            self::PUBLISHED => 'published',
            self::ARCHIVED => 'archived',
        };
    }
}
