<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\DTO;

class KameleoonUserData
{
    /**
     * @param int $index the index of the custom data, should be taken from Kameleoon dashboard
     * @param string $value
     */
    public function __construct(
        public readonly int $index,
        public readonly string $value,
    ) {
    }
}
