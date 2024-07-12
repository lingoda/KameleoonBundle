<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\DTO;

use Lingoda\KameleoonBundle\Enum\KameleoonCustomDataEnum;

class KameleoonUserData
{
    public function __construct(
        public readonly KameleoonCustomDataEnum $id,
        public readonly string | int | float | bool $value,
    ) {
    }
}
