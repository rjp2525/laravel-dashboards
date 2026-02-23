<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Acl\AclManager;
use Reno\Dashboard\Acl\PolicyAclDriver;
use Reno\Dashboard\Acl\SpatieAclDriver;
use Reno\Dashboard\Contracts\Authorizable;
use Reno\Dashboard\Widgets\AbstractWidget;

test('PolicyAclDriver check() delegates to subject authorize() method', function (): void {
    $driver = new PolicyAclDriver;
    $user = mock(Authenticatable::class);

    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(true);

    expect($driver->check($user, $subject))->toBeTrue();
});

test('PolicyAclDriver check() returns true when authorize() returns true', function (): void {
    $driver = new PolicyAclDriver;
    $user = mock(Authenticatable::class);

    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(true);

    expect($driver->check($user, $subject))->toBeTrue();
});

test('PolicyAclDriver check() returns false when authorize() returns false', function (): void {
    $driver = new PolicyAclDriver;
    $user = mock(Authenticatable::class);

    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(false);

    expect($driver->check($user, $subject))->toBeFalse();
});

test('SpatieAclDriver check() throws RuntimeException when user lacks hasPermissionTo method', function (): void {
    $driver = new SpatieAclDriver;
    $user = mock(Authenticatable::class);

    // Create an AbstractWidget mock that extends AbstractWidget (which is Authorizable)
    $widget = mock(AbstractWidget::class)->makePartial();
    $widget->shouldReceive('getRequiredPermissions')->andReturn(['view-dashboard']);

    $driver->check($user, $widget);
})->throws(RuntimeException::class, 'Spatie ACL driver requires spatie/laravel-permission');

test('SpatieAclDriver check() returns true when widget has no required permissions', function (): void {
    $driver = new SpatieAclDriver;

    $user = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 1;
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return '';
        }

        public function hasPermissionTo(string $permission): bool
        {
            return false;
        }
    };

    $widget = mock(AbstractWidget::class)->makePartial();
    $widget->shouldReceive('getRequiredPermissions')->andReturn([]);

    expect($driver->check($user, $widget))->toBeTrue();
});

test('SpatieAclDriver check() calls hasPermissionTo on user', function (): void {
    $driver = new SpatieAclDriver;

    $user = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 1;
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return '';
        }

        public function hasPermissionTo(string $permission): bool
        {
            return true;
        }
    };

    $widget = mock(AbstractWidget::class)->makePartial();
    $widget->shouldReceive('getRequiredPermissions')->andReturn(['view-dashboard']);

    expect($driver->check($user, $widget))->toBeTrue();
});

test('SpatieAclDriver check() returns false when user lacks permission', function (): void {
    $driver = new SpatieAclDriver;

    $user = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 1;
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getRememberToken(): ?string
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return '';
        }

        public function hasPermissionTo(string $permission): bool
        {
            return false;
        }
    };

    $widget = mock(AbstractWidget::class)->makePartial();
    $widget->shouldReceive('getRequiredPermissions')->andReturn(['admin-dashboard']);

    expect($driver->check($user, $widget))->toBeFalse();
});

it('AclManager resolves PolicyAclDriver by default', function (): void {
    $manager = app(AclManager::class);

    $user = mock(Authenticatable::class);
    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(true);

    // Default config is 'policy'
    expect($manager->check($user, $subject))->toBeTrue();
});

it('AclManager can resolve with policy driver config', function (): void {
    config()->set('dashboard.acl.driver', 'policy');

    $manager = app(AclManager::class);

    $user = mock(Authenticatable::class);
    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(false);

    expect($manager->check($user, $subject))->toBeFalse();
});

it('AclManager custom driver config creates from app container', function (): void {
    config()->set('dashboard.acl.driver', 'custom');
    config()->set('dashboard.acl.custom_driver', PolicyAclDriver::class);

    $manager = app(AclManager::class);

    $user = mock(Authenticatable::class);
    $subject = mock(Authorizable::class);
    $subject->shouldReceive('authorize')->once()->with($user)->andReturn(true);

    expect($manager->check($user, $subject))->toBeTrue();
});
