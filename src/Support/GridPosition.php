<?php

namespace Reno\Dashboard\Support;

class GridPosition
{
    public function __construct(
        public int $x = 0,
        public int $y = 0,
        public int $w = 4,
        public int $h = 2,
        public ?int $minW = null,
        public ?int $maxW = null,
        public ?int $minH = null,
        public ?int $maxH = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            x: self::toInt($data['x'] ?? null, 0),
            y: self::toInt($data['y'] ?? null, 0),
            w: self::toInt($data['w'] ?? null, 4),
            h: self::toInt($data['h'] ?? null, 2),
            minW: self::toOptionalInt($data['min_w'] ?? null),
            maxW: self::toOptionalInt($data['max_w'] ?? null),
            minH: self::toOptionalInt($data['min_h'] ?? null),
            maxH: self::toOptionalInt($data['max_h'] ?? null),
        );
    }

    private static function toInt(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private static function toOptionalInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /** @return array<string, int|null> */
    public function toArray(): array
    {
        return array_filter([
            'x' => $this->x,
            'y' => $this->y,
            'w' => $this->w,
            'h' => $this->h,
            'min_w' => $this->minW,
            'max_w' => $this->maxW,
            'min_h' => $this->minH,
            'max_h' => $this->maxH,
        ], fn (?int $v): bool => $v !== null);
    }
}
