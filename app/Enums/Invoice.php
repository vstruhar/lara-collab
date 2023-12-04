<?php

namespace App\Enums;

enum Invoice: string
{
    case CREATED = 'created';
    case SENT = 'sent';
    case PAID = 'paid';
}
