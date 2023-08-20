<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatusesEnum: string
{
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Frozen = 'frozen';
}
