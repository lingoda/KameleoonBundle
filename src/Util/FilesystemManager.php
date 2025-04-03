<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Util;

final class FilesystemManager
{
    public function createDir(string $path, int $permissions = 0755): void
    {
        if (!is_dir($path)) {
            @mkdir($path, $permissions, true);
        }
    }

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

                $path = sprintf(
                    '%s%s%s',
                    $dir,
                    str_ends_with($dir, DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR,
                    $item
                );

                if (is_dir($path)) {
                    chmod($path, $permissions);
                    $this->changeDirPermissions($path, $permissions);
                } elseif (is_file($path)) {
                    chmod($path, $permissions);
                }
            }
        }
    }
}
