<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Routing\Matching;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Matching\GenericRouteMatcher;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class GenericRouteMatcherTest extends TestCase
{
    private RouteConfig $routeConfig;

    private GenericRouteMatcher $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeConfig = new RouteConfig();

        $method = new MethodReflector(new ReflectionMethod(self::class, 'dummyMethod'));
        $this->routeConfig->addRoute($method, new Route('/static', Method::GET));
        $this->routeConfig->addRoute($method, new Route('/dynamic/{id}', Method::GET));
        $this->routeConfig->addRoute($method, new Route('/dynamic/{id}/view', Method::GET));
        $this->routeConfig->addRoute($method, new Route('/dynamic/{id}/{tag}/{name}/{id}', Method::GET));

        $this->subject = new GenericRouteMatcher($this->routeConfig);
    }

    public function test_match_on_static_route(): void
    {
        $request = new ServerRequest(uri: '/static', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals([], $matchedRoute->params);
        $this->assertFalse($matchedRoute->route->isDynamic);
        $this->assertEquals('/static', $matchedRoute->route->uri);
    }

    public function test_match_returns_null_on_unknown_route(): void
    {
        $request = new ServerRequest(uri: '/non-existing', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertNull($matchedRoute);
    }

    public function test_match_returns_null_on_unconfigured_method(): void
    {
        $request = new ServerRequest(uri: '/static', method: 'POST');

        $matchedRoute = $this->subject->match($request);

        $this->assertNull($matchedRoute);
    }

    public function test_match_on_dynamic_route(): void
    {
        $request = new ServerRequest(uri: '/dynamic/5', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals([ 'id' => '5' ], $matchedRoute->params);
        $this->assertTrue($matchedRoute->route->isDynamic);
        $this->assertEquals('/dynamic/{id}', $matchedRoute->route->uri);
    }

    public function test_match_on_dynamic_route_with_many_parameters(): void
    {
        $request = new ServerRequest(uri: '/dynamic/5/brendt/brent/6', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals([ 'id' => '6', 'tag' => 'brendt', 'name' => 'brent' ], $matchedRoute->params);
        $this->assertTrue($matchedRoute->route->isDynamic);
        $this->assertEquals('/dynamic/{id}/{tag}/{name}/{id}', $matchedRoute->route->uri);
    }

    public static function dummyMethod(): Response
    {
        return new Ok();
    }
}