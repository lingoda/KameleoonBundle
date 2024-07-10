<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

use Kameleoon\KameleoonClient as KameleoonClientInterface;
use Kameleoon\KameleoonClientFactory;

class KameleoonClient
{
    private ?KameleoonClientInterface $client = null;

    public function __construct(
        private readonly KameleoonConfig $kameleoonConfig
    ) {
    }

    public function getClient(): KameleoonClientInterface
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = KameleoonClientFactory::createWithConfig(
            $this->kameleoonConfig->getKameleoonSiteCode(),
            $this->kameleoonConfig->getConfig(),
        );
        return $this->client;
    }
}
