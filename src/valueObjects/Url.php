<?php

namespace app\valueObjects;

use app\exceptions\InvalidUrlException;

class Url
{
    public const SCHEME_HTTP = 'http';
    public const SCHEME_HTTPS = 'https';
    public const ALLOWED_SCHEMES = [
        self::SCHEME_HTTP,
        self::SCHEME_HTTPS,
    ];

    /** @var array|false|int|string|null */
    private $parts;

    /**
     * @param string $url
     * @throws InvalidUrlException
     */
    public function __construct(string $url)
    {
        $this->parts = $this->buildParts($url);
        if (!isset($this->parts['scheme'])) {
            $url = (string)$this;
            if (\substr($url, 0, 2) === '//') {
                $url = \substr($url, 2);
            }
            $url = self::SCHEME_HTTP . '://' . $url;
            $this->parts = $this->buildParts($url);
        }
        $this->assertScheme();
    }

    public function getScheme(): string
    {
        return $this->parts['scheme'];
    }

    /**
     * @param string $scheme
     * @return $this
     * @throws InvalidUrlException
     */
    public function setScheme(string $scheme): self
    {
        $this->parts['scheme'] = $scheme;
        $this->assertScheme();

        return $this;
    }

    public function addPath(string $path): self
    {
        $path = $this->getPath() . $path;
        $path = str_replace('//', '/', $path);
        $this->parts['path'] = $path;

        return $this;
    }

    public function getHost(): string
    {
        return $this->parts['host'];
    }

    public function getPath(): string
    {
        return $this->parts['path'] ?? '';
    }

    public function __toString(): string
    {
        return $this->buildUrl($this->parts);
    }

    private function buildUrl(array $parts): string
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }

    private function buildParts(string $url): array
    {
        $parts = \parse_url($url);
        if (!$parts) {
            throw new InvalidUrlException('Failed to build a URL: ' . $url);
        }

        return $parts;
    }

    private function assertScheme(): void
    {
        if (!\in_array($this->parts['scheme'], self::ALLOWED_SCHEMES, true)) {
            throw new InvalidUrlException('Scheme is not allowed');
        }
    }
}