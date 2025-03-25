<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cache;

use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly KameleoonConfig $config
    )
    {
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->warmUpWorkingDir();

        return [];
    }

    private function warmUpWorkingDir(): void
    {
        if (!is_dir($this->config->getConfig()->getKameleoonWorkDir())) {
            @mkdir($this->config->getConfig()->getKameleoonWorkDir(), 0755, true);
        }
    }
}
