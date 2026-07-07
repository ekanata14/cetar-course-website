<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Rejected = 'rejected';
}
