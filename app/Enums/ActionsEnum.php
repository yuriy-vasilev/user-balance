<?php

declare(strict_types=1);

namespace App\Enums;

enum ActionsEnum: string
{
    case Add = 'add';
    case Subtract = 'subtract';
    case Transfer = 'transfer';
    case Freeze = 'freeze';
    case Approve = 'approve';
    case Reject = 'reject';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
