<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\DTO;

class KameleoonFeatureFlagData
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly bool $enabled,
    ) {
    }
}
