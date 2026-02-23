<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Reno\Dashboard\Http\Middleware\AuthorizeDashboard;
use Reno\Dashboard\Models\Dashboard;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

it('allows access when no slug is present', function (): void {
    $middleware = new AuthorizeDashboard;

    $request = Request::create('/dashboard', 'GET');
    $request->setRouteResolver(function (): Route {
        $route = new Route('GET', '/dashboard/{slug?}', []);
        $route->bind(Request::create('/dashboard', 'GET'));

        return $route;
    });

    $response = $middleware->handle($request, function ($req): Response {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
    expect($response->getStatusCode())->toBe(200);
});

it('returns 404 when dashboard slug not found', function (): void {
    $middleware = new AuthorizeDashboard;

    $request = Request::create('/dashboard/nonexistent', 'GET');
    $request->setRouteResolver(function (): Route {
        $route = new Route('GET', '/dashboard/{slug?}', []);
        $route->bind(Request::create('/dashboard/nonexistent', 'GET'));

        return $route;
    });

    $middleware->handle($request, function ($req): Response {
        return new Response('OK');
    });
})->throws(NotFoundHttpException::class);

it('allows access when dashboard exists', function (): void {
    Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $middleware = new AuthorizeDashboard;

    $request = Request::create('/dashboard/main', 'GET');
    $request->setRouteResolver(function (): Route {
        $route = new Route('GET', '/dashboard/{slug?}', []);
        $route->bind(Request::create('/dashboard/main', 'GET'));

        return $route;
    });

    $response = $middleware->handle($request, function ($req): Response {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
    expect($response->getStatusCode())->toBe(200);
});
