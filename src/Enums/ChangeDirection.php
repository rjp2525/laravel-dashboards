<?php

namespace Reno\Dashboard\Enums;

enum ChangeDirection: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
    case NEUTRAL = 'neutral';

    public static function fromChange(float|int|null $change): self
    {
        if ($change === null || $change == 0) {
            return self::NEUTRAL;
        }

        return $change > 0 ? self::POSITIVE : self::NEGATIVE;
    }
}
