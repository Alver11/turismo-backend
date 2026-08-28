<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class RouteRegistrationTest extends TestCase
{
    public function test_api_v1_routes_are_registered(): void
    {
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1/'));

        $this->assertCount(64, $routes);
        $this->assertTrue($routes->contains(fn (Route $route): bool => $route->uri() === 'api/v1/login'
            && in_array('POST', $route->methods(), true)));
        $this->assertTrue($routes->contains(fn (Route $route): bool => $route->uri() === 'api/v1/tourists/{tourist}'
            && in_array('DELETE', $route->methods(), true)));
    }

    public function test_protected_routes_keep_sanctum_middleware(): void
    {
        $route = collect(RouteFacade::getRoutes()->getRoutes())
            ->first(fn (Route $route): bool => $route->uri() === 'api/v1/users');

        $this->assertNotNull($route);
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
    }

    public function test_ai_route_is_authenticated_authorized_and_rate_limited(): void
    {
        $route = collect(RouteFacade::getRoutes()->getRoutes())
            ->first(fn (Route $route): bool => $route->uri() === 'api/v1/ask');

        $this->assertNotNull($route);
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertContains('permission:ai ask', $route->gatherMiddleware());
        $this->assertContains('throttle:ai', $route->gatherMiddleware());

        $this->postJson('/api/v1/ask', ['question' => '¿Qué puedo visitar?'])
            ->assertUnauthorized();
    }

    public function test_administrative_routes_require_module_permissions(): void
    {
        $route = collect(RouteFacade::getRoutes()->getRoutes())
            ->first(fn (Route $route): bool => $route->uri() === 'api/v1/users'
                && in_array('GET', $route->methods(), true));

        $this->assertNotNull($route);
        $this->assertContains('permission:program users', $route->gatherMiddleware());

        $deleteRoute = collect(RouteFacade::getRoutes()->getRoutes())
            ->first(fn (Route $route): bool => $route->uri() === 'api/v1/users/{user}'
                && in_array('DELETE', $route->methods(), true));

        $this->assertNotNull($deleteRoute);
        $this->assertContains('permission:users delete', $deleteRoute->gatherMiddleware());
    }
}
