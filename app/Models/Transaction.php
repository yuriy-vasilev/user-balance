<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatusesEnum;

/**
 * @property int $id
 * @property string $identifier
 * @property int $userId
 * @property float $amount
 * @property string $action
 * @property string $status
 */
class Transaction extends BaseModel
{
    protected $table = 'transactions';

    public static function getCompleted(string $identifier, int $userId): ?self
    {
        return self::where('identifier', $identifier)
            ->where('user_id', $userId)
            ->where('status', TransactionStatusesEnum::Completed->value)
            ->first();
    }

    public static function getFrozenWithLock(string $identifier): ?self
    {
        return self::where('identifier', $identifier)
            ->where('status', TransactionStatusesEnum::Frozen->value)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
