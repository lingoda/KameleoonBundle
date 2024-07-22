<?php

declare(strict_types=1);

namespace Lingoda\KameleoonBundle\User;

interface UserInterface
{
    public function getEmail(): string;
}
