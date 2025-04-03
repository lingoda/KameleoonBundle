<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Util;

final class FilesystemManager
{
    public function deleteDir(string $dir): void
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

    public function changeDirPermissions(string $dir, int $permissions): void
    {
        $content = scandir($dir);

        if (!empty($content)) {
            foreach ($content as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                if (is_dir($item)) {
                    chmod($item, $permissions);
                    $this->changeDirPermissions($item, $permissions);
                } elseif (is_file($item)) {
                    chmod($item, $permissions);
                }
            }
        }
    }
}