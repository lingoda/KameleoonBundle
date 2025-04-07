<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cache;

use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Lingoda\KameleoonBundle\Util\FilesystemManager;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class CacheClearer implements CacheClearerInterface
{
    private FilesystemManager $filesystem;

    public function __construct(
        private readonly KameleoonConfig $config
    )
    {
        $this->filesystem = new FilesystemManager();
    }

    public function clear(string $cacheDir)
    {
        $this->deleteWorkingDir();
    }

    private function deleteWorkingDir(): void
    {
        if (is_dir($this->config->getConfig()->getKameleoonWorkDir())) {
            $this->filesystem->deleteDir($this->config->getConfig()->getKameleoonWorkDir());
        }
    }


}
