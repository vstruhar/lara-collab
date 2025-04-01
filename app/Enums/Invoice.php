<?php

namespace App\Enums;

enum Invoice: string
{
    case STATUS_NEW = 'new';
    case STATUS_SENT = 'sent';
    case STATUS_PAID = 'paid';

    case TYPE_DEFAULT = 'default'; // this will used hourly or fixed amount from task
    case TYPE_FIXED_AMOUNT = 'fixed_amount'; // this will used fixed amount for whole invoice
}
