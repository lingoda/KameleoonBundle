<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use JsonException;
use Kameleoon\CookieOptions;
use Kameleoon\KameleoonClientConfig;

class KameleoonConfig
{
    private bool $cookieSecure = false;
    private bool $cookieHttpOnly = false;
    private string $cookieSameSite = "Lax";
    private string $configFileName = 'kameleoon.json';

    public function __construct(
        private readonly KameleoonEnvironmentMapper $kameleoonEnvironmentMapper,
        private readonly string $kameleoonClientId,
        private readonly string $kameleoonClientSecret,
        private readonly string $kameleoonSiteCode,
        private readonly bool $kameleoonDebugMode,
        private readonly string $kameleoonWorkDir,
        private readonly int $kameleoonRefreshInterval,
        private readonly int $kameleoonDefaultTimeout,
        private readonly string $cookieDomain,
    ) {
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
            $this->kameleoonEnvironmentMapper->getEnvironment(),
        );
    }

    private function getCookieOptions(): CookieOptions
    {
        return KameleoonClientConfig::createCookieOptions(
            $this->cookieDomain,
            $this->cookieSecure,
            $this->cookieHttpOnly,
            $this->cookieSameSite,
        );
    }

    public function getKameleoonSiteCode(): string
    {
        return $this->kameleoonSiteCode;
    }

    /**
     * @throws JsonException
     */
    private function getJsonConfig(): string
    {
        return json_encode([
            'client_id' => $this->kameleoonClientId,
            'client_secret' => $this->kameleoonClientSecret,
            'refresh_interval_minute' => $this->kameleoonRefreshInterval,
            'default_timeout_millisecond' => $this->kameleoonDefaultTimeout,
            'cookie_options' => [
                'domain' => $this->cookieDomain,
                'secure' => $this->cookieSecure,
                'http_only' => $this->cookieHttpOnly,
                'samesite' => $this->cookieSameSite,
            ],
            'debug_mode' => $this->kameleoonDebugMode,
            'environment' => $this->kameleoonEnvironmentMapper->getEnvironment(),
        ], JSON_THROW_ON_ERROR);
    }

    private function getConfigFilePath(): string
    {
        return $this->kameleoonWorkDir . '/' . $this->configFileName;
    }

    public function writeConfigFile(): string
    {
        // make sure the directory exists
        if (!is_dir($this->kameleoonWorkDir)) {
            mkdir($this->kameleoonWorkDir, 0777, true);
        }
        $path = $this->getConfigFilePath();
        file_put_contents($path, $this->getJsonConfig());
        return $path;
    }
}
