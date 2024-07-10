<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\Kameleoon;

class KameleoonEnvironmentMapper
{
    public function __construct(
        private readonly string $environment
    ) {
    }

    public function getEnvironment(): string
    {
        return match (true) {
            $this->environment === 'prod' => 'production',
            $this->environment === 'release' => 'release',
            str_starts_with($this->environment, 'pr-') => 'pullrequest',
            $this->environment === 'staging' => 'staging',
            default => 'development',
        };
    }
}
