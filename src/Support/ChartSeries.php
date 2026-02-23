<?php

namespace Reno\Dashboard\Support;

class ChartSeries
{
    /** @param array<int, int|float> $data */
    public function __construct(
        public readonly string $name,
        public readonly array $data = [],
        public readonly ?string $type = null,
        public readonly ?string $color = null,
    ) {}

    /** @param array<int, int|float> $data */
    public static function make(string $name, array $data = []): self
    {
        return new self($name, $data);
    }

    public function withType(string $type): self
    {
        return new self($this->name, $this->data, $type, $this->color);
    }

    public function withColor(string $color): self
    {
        return new self($this->name, $this->data, $this->type, $color);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'data' => $this->data,
            'type' => $this->type,
            'color' => $this->color,
        ], fn (string|array|null $v): bool => $v !== null);
    }
}
