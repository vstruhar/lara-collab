<?php

namespace App\Enums;

enum Invoice: string
{
    case STATUS_NEW = 'new';
    case STATUS_SENT = 'sent';
    case STATUS_PAID = 'paid';

    case TYPE_HOURLY = 'hourly';
    case TYPE_FIXED_AMOUNT = 'fixed_amount';
}
