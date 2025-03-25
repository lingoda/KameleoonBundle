<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Cache;

use Lingoda\KameleoonBundle\Kameleoon\KameleoonConfig;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class CacheClearer implements CacheClearerInterface
{
    public function __construct(
        private readonly KameleoonConfig $config
    )
    {
    }

    public function clear(string $cacheDir)
    {
        $this->deleteWorkingDir();
    }

    private function deleteWorkingDir(): void
    {
        if (is_dir($this->config->getConfig()->getKameleoonWorkDir())) {
            $this->deleteDir($this->config->getConfig()->getKameleoonWorkDir());
        }
    }

    private function deleteDir(string $dir): void
    {
        $content = scandir($dir);

        if (!empty($content)) {
            foreach ($content as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $item;
                is_dir($path) ? $this->deleteDir($path) : unlink($path);
            }
        }

        rmdir($dir);
    }
}
