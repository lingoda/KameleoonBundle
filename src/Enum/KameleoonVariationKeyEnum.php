<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Enum;

enum KameleoonVariationKeyEnum: string
{
    case VARIANT = 'variant';

    case CONTROL = 'control';

    case OFF = 'off';

    case ON = 'on';

    /**
     * @return KameleoonVariationKeyEnum[]
     */
    public static function getVariantChoices(): array
    {
        return [
            self::VARIANT,
            self::ON,
        ];
    }

    public static function isVariant(KameleoonVariationKeyEnum $variationKey): bool
    {
        return in_array($variationKey, self::getVariantChoices(), true);
    }
}
