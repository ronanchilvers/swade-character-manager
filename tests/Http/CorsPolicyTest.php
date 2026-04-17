<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\CorsPolicy;
use PHPUnit\Framework\TestCase;

class CorsPolicyTest extends TestCase
{
    public function testIsEnabledReturnsFalseWhenOriginsAreMissingOrInvalid(): void
    {
        self::assertFalse(CorsPolicy::isEnabled([]));
        self::assertFalse(CorsPolicy::isEnabled(['origins' => ['   ', 10]]));
    }

    public function testResolveReturnsHeadersForMatchingOriginAndOptions(): void
    {
        $headers = CorsPolicy::resolve(
            [
                'origins' => ['https://app.example.com/'],
                'methods' => ['GET', 'POST'],
                'headers' => ['Content-Type', 'Authorization'],
                'expose_headers' => ['X-Trace-Id'],
                'allow_credentials' => true,
                'max_age' => 600,
            ],
            'https://APP.example.com'
        );

        self::assertSame(
            [
                'Access-Control-Allow-Origin' => 'https://app.example.com',
                'Vary' => 'Origin',
                'Access-Control-Allow-Methods' => 'GET, POST',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                'Access-Control-Expose-Headers' => 'X-Trace-Id',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '600',
            ],
            $headers
        );
    }

    public function testResolveReturnsEmptyForMismatchedOrBlankOrigin(): void
    {
        $config = ['origins' => ['https://api.example.com']];

        self::assertSame([], CorsPolicy::resolve($config, 'https://other.example.com'));
        self::assertSame([], CorsPolicy::resolve($config, ' '));
    }

    public function testResolveSupportsWildcardAndFallsBackToDefaultMethodsAndHeaders(): void
    {
        $headers = CorsPolicy::resolve(
            [
                'origins' => ['*'],
                'methods' => [' ', ''],
                'headers' => [],
            ],
            'https://client.example.com'
        );

        self::assertSame('https://client.example.com', $headers['Access-Control-Allow-Origin']);
        self::assertSame('GET, POST, OPTIONS', $headers['Access-Control-Allow-Methods']);
        self::assertSame('Content-Type, Authorization', $headers['Access-Control-Allow-Headers']);
    }
}
