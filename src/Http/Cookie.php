<?php

declare(strict_types=1);

namespace App\Http;

use flight\net\Request;

class Cookie
{
    public const SAMESITE_NONE   = "None";
    public const SAMESITE_LAX    = "Lax";
    public const SAMESITE_STRICT = "Strict";

    public static function get(Request $request, string $name)
    {
        $object = new static($name);
        $cookies = $request->cookies;
        if (isset($cookies[$name])) {
            return $object->data($cookies[$name]);
        }

        return $object;
    }

    public static function create(string $name, mixed $data)
    {
        $cookie = new static($name);

        return $cookie->data($data);
    }

    private string $name;
    private ?string $data;
    private int $expires;
    private string $path;
    private ?string $domain;
    private bool $secure;
    private bool $httpOnly;
    private string $sameSite;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->data = null;
        $this->expires = 0;
        $this->path = "/";
        $this->domain = null;
        $this->secure = true;
        $this->httpOnly = false;
        $this->sameSite = static::SAMESITE_LAX;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function name(string $name): static
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function data(?string $data): static
    {
        $new = clone $this;
        $new->data = $data;

        return $new;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function expires(int $seconds): static
    {
        $new = clone $this;
        $new->expires = time() + $seconds;

        return $new;
    }

    public function forever(): static
    {
        $new = clone $this;
        $new->expires = 0;

        return $new;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function path(string $path): static
    {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function domain(?string $domain): static
    {
        $new = clone $this;
        $new->domain = $domain;

        return $new;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function secure(bool $secure): static
    {
        $new = clone $this;
        $new->secure = $secure;

        return $new;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function httpOnly(bool $httpOnly): static
    {
        $new = clone $this;
        $new->httpOnly = $httpOnly;

        return $new;
    }

    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    public function sameSite(string $sameSite): static
    {
        $new = clone $this;
        $new->sameSite = $sameSite;

        return $new;
    }

    public function set(): bool
    {
        $options = [
            'expires' => $this->expires,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        return setcookie(
            $this->name,
            $this->data,
            $options
        );
    }
}
