<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $userId
 * @property float $balance
 * @property float $availableBalance
 */
class Balance extends BaseModel
{
    use HasFactory;

    public $fillable = ['user_id'];

    public $timestamps = false;

    protected $table = 'balances';

    public function increment($column, $amount = 1, array $extra = [])
    {
        return parent::increment($column, $amount, $extra);
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        return parent::decrement($column, $amount, $extra);
    }

    public static function getByUserId(int $userId): ?self
    {
        return self::where('user_id', $userId)->firstOrFail();
    }

    public static function getByUserIdWithLock(int $userId): ?self
    {
        return self::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }
}
