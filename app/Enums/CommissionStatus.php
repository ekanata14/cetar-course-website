<?php

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Cancelled = 'cancelled';
}
