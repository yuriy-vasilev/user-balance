<?php

declare(strict_types=1);

namespace Tests\DataProviders;

use App\Enums\ActionsEnum;

trait TransactionServiceDataProvider
{
    public static function providerUpdateBalance(): array
    {
        return [
            'actionAdd' => [
                ActionsEnum::Add,
                25,
                [
                    'balance' => 125,
                    'availableBalance' => 125,
                ],
            ],
            'actionSubtract' => [
                ActionsEnum::Subtract,
                25,
                [
                    'balance' => 75,
                    'availableBalance' => 75,
                ],
            ],
        ];
    }

    public static function providerUpdateBalanceFailed(): array
    {
        return [
            'actionSubtractFailed' => [
                ActionsEnum::Subtract,
                25,
                9999,
            ],
            'actionFreezeFailed' => [
                ActionsEnum::Freeze,
                25,
                9999,
            ],
        ];
    }
}
