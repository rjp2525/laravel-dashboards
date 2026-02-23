<?php

use Reno\Dashboard\Acl\PolicyAclDriver;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;
use Reno\Dashboard\Tests\Fixtures\TestUser;

it('allows access when widget has no required permissions', function (): void {
    $widget = new TestStatWidget;
    $driver = new PolicyAclDriver;

    $user = new TestUser(['id' => 1]);

    expect($driver->check($user, $widget))->toBeTrue();
});

it('widget authorize returns true by default', function (): void {
    $widget = new TestStatWidget;

    $user = new TestUser(['id' => 1]);

    expect($widget->authorize($user))->toBeTrue();
});
