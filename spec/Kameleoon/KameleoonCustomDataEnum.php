<?php

declare(strict_types=1);
namespace spec\Lingoda\KameleoonBundle\Kameleoon;

enum KameleoonCustomDataEnum: int
{
    case IS_LONGOODIE = 0;
    case SECTION = 1;
    case MODULE = 2;

    case ACTIVE_SUBSCRIBER = 5;
    case SUBSCRIPTION_TYPE = 6;
    case PACKAGE_SIZE = 7;
    case IS_B2B = 8;
    case IS_STUDENT = 9;
    case IS_TEACHER = 10;
}

// @TODO update this enum. Or we maybe could remove it? 
