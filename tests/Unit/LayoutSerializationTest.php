<?php

use Reno\Dashboard\Support\GridPosition;

it('creates grid position with defaults', function (): void {
    $pos = new GridPosition;

    expect($pos->x)->toBe(0);
    expect($pos->y)->toBe(0);
    expect($pos->w)->toBe(4);
    expect($pos->h)->toBe(2);
});

it('serializes to array', function (): void {
    $pos = new GridPosition(x: 1, y: 2, w: 6, h: 3);

    expect($pos->toArray())->toBe([
        'x' => 1,
        'y' => 2,
        'w' => 6,
        'h' => 3,
    ]);
});

it('serializes with optional min/max', function (): void {
    $pos = new GridPosition(x: 0, y: 0, w: 4, h: 2, minW: 2, maxW: 8);

    $arr = $pos->toArray();

    expect($arr)->toHaveKey('min_w', 2);
    expect($arr)->toHaveKey('max_w', 8);
    expect($arr)->not->toHaveKey('min_h');
    expect($arr)->not->toHaveKey('max_h');
});

it('deserializes from array', function (): void {
    $pos = GridPosition::fromArray([
        'x' => 3,
        'y' => 1,
        'w' => 6,
        'h' => 4,
        'min_w' => 2,
    ]);

    expect($pos->x)->toBe(3);
    expect($pos->y)->toBe(1);
    expect($pos->w)->toBe(6);
    expect($pos->h)->toBe(4);
    expect($pos->minW)->toBe(2);
    expect($pos->maxW)->toBeNull();
});

it('round-trips through serialization', function (): void {
    $original = new GridPosition(x: 2, y: 3, w: 4, h: 5, minW: 1, maxH: 10);

    $restored = GridPosition::fromArray($original->toArray());

    expect($restored->x)->toBe($original->x);
    expect($restored->y)->toBe($original->y);
    expect($restored->w)->toBe($original->w);
    expect($restored->h)->toBe($original->h);
    expect($restored->minW)->toBe($original->minW);
    expect($restored->maxH)->toBe($original->maxH);
});
