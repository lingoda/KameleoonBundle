<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cache;

use Kameleoon\KameleoonClient;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private readonly KameleoonConfig $config,
        private readonly KameleoonClient $client,
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
        $this->client->getFeatureList();

        $files = scandir($this->config->getConfig()->getKameleoonWorkDir());

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $path = sprintf('%s/%s', $this->config->getConfig()->getKameleoonWorkDir(), $file);

            if (is_file($path)) {
                chmod($path, 0777);
            }
        }
    }
}
