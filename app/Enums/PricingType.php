<?php

namespace App\Enums;

enum PricingType: string
{
    case HOURLY = 'hourly';
    case FIXED = 'fixed';
}
