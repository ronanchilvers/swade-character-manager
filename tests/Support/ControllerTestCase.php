<?php

declare(strict_types=1);

namespace Tests\Support;

use flight\Engine;
use Flight;
use PHPUnit\Framework\TestCase;

abstract class ControllerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Flight::setEngine(new Engine());
        $_GET = [];
        $_POST = [];
    }

    protected function mapRenderToException(): void
    {
        Flight::map('render', function (string $template, array $data = []): void {
            throw new RenderedResponse($template, $data);
        });
    }

    protected function mapRedirectToException(): void
    {
        Flight::map('redirect', function (string $url): void {
            throw new RedirectedResponse($url);
        });
    }

    protected function mapRequest(string $method = 'GET', array $query = [], string $body = '', string $url = '/'): RequestStub
    {
        $request = new RequestStub($method, $query, $body, $url);
        Flight::map('request', fn (): RequestStub => $request);

        return $request;
    }

    protected function mapSession(?FlashSession $session = null): FlashSession
    {
        $session ??= new FlashSession();
        Flight::map('session', fn (): FlashSession => $session);

        return $session;
    }

    protected function mapResponse(?JsonResponse $response = null): JsonResponse
    {
        $response ??= new JsonResponse();
        Flight::map('response', fn (): JsonResponse => $response);

        return $response;
    }

    protected function mapJsonToResponse(?JsonResponse $response = null): JsonResponse
    {
        $response = $this->mapResponse($response);
        Flight::map('json', function (array $data, int $code = 200) use ($response): void {
            $response
                ->status($code)
                ->header('Content-Type', 'application/json')
                ->write((string) json_encode($data));
        });

        return $response;
    }

    protected function mapUrls(array $routes = []): FlightUrlMap
    {
        $urlMap = new FlightUrlMap($routes);
        Flight::map('getUrl', fn (string $alias, array $params = []): string => $urlMap->url($alias, $params));

        return $urlMap;
    }
}
