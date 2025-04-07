<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cache;

use Kameleoon\KameleoonClient;
use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Lingoda\KameleoonBundle\Util\FilesystemManager;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class CacheWarmer implements CacheWarmerInterface
{
    private const DEFAULTS = [
        'tmp_test'
    ];

    private FilesystemManager $filesystem;

    public function __construct(
        private readonly KameleoonConfig $config,
        private readonly KameleoonClient $client,
    )
    {
        $this->filesystem = new FilesystemManager();
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->warmUpWorkingDir();

        if (!in_array($this->config->getConfig()->getClientId(), self::DEFAULTS, true)) {
            $this->warmUpConfigFile();
        }

        return [];
    }

    private function warmUpWorkingDir(): void
    {
        $this->filesystem->createDir($this->config->getConfig()->getKameleoonWorkDir(), 0777);
    }

    private function warmUpConfigFile(): void
    {
        $this->client->getFeatureList();
        $this->filesystem->changeDirPermissions(
            $this->config->getConfig()->getKameleoonWorkDir(),
            0777,
        );
    }
}
