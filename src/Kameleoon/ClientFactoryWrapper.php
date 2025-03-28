<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\KameleoonClient;
use Kameleoon\KameleoonClientFactory as KameleoonSDKClientFactory;

class ClientFactoryWrapper
{
    public static function createWithConfig(KameleoonConfig $config): KameleoonClient
    {
        return KameleoonSDKClientFactory::createWithConfig(
            $config->getKameleoonSiteCode(),
            $config->getConfig()
        );
    }
}
