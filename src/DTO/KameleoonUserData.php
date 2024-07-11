<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\DTO;

use Lingoda\KameleoonBundle\Enum\KameleoonCustomDataEnum;

readonly class KameleoonUserData
{
    public function __construct(
        public KameleoonCustomDataEnum $id,
        public string | int | float | bool $value,
    ) {
    }
}
