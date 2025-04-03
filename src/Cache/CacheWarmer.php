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
        $this->warmUpConfigFile();

        return [];
    }

    private function warmUpWorkingDir(): void
    {
        if (!is_dir($this->config->getConfig()->getKameleoonWorkDir())) {
            @mkdir($this->config->getConfig()->getKameleoonWorkDir(), 0777, true);
        }
    }

    private function warmUpConfigFile(): void
    {
        if (!file_exists($this->config->getConfigurationFilePath())) {
            file_put_contents($this->config->getConfigurationFilePath(), json_encode([]));
            chmod($this->config->getConfigurationFilePath(), 0777);
        }
    }
}
