<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use JsonException;
use Kameleoon\CookieOptions;
use Kameleoon\KameleoonClientConfig;

class KameleoonConfig
{
    /**
     * @param array<string,mixed> $kameleoonCookieOptions
     */
    public function __construct(
        private readonly string $environment,
        private readonly string $kameleoonClientId,
        private readonly string $kameleoonClientSecret,
        private readonly string $kameleoonSiteCode,
        private readonly bool   $kameleoonDebugMode,
        private readonly string $kameleoonWorkDir,
        private readonly int    $kameleoonRefreshInterval,
        private readonly int    $kameleoonDefaultTimeout,
        private readonly array  $kameleoonCookieOptions,
    )
    {
    }

    public function getConfig(): KameleoonClientConfig
    {
        return new KameleoonClientConfig(
            $this->kameleoonClientId,
            $this->kameleoonClientSecret,
            $this->kameleoonWorkDir,
            $this->kameleoonRefreshInterval,
            $this->kameleoonDefaultTimeout,
            $this->kameleoonDebugMode,
            $this->getCookieOptions(),
            $this->environment,
        );
    }

    public function getKameleoonSiteCode(): string
    {
        return $this->kameleoonSiteCode;
    }

    /* @phpstan-ignore-next-line */
    private function getCookieOptions(): CookieOptions
    {
        return KameleoonClientConfig::createCookieOptions(
            $this->kameleoonCookieOptions['domain'],
            $this->kameleoonCookieOptions['secure'],
            $this->kameleoonCookieOptions['http_only'],
            $this->kameleoonCookieOptions['same_site'],
        );
    }
}
