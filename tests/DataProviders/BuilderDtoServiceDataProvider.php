<?php

declare(strict_types=1);

namespace Tests\DataProviders;

use App\Enums\ActionsEnum;

trait BuilderDtoServiceDataProvider
{
    public static function providerCases(): array
    {
        return [
            'actionAddNewUser' => [
                [
                    'sender' => 99,
                    'amount' => 3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Add->value,
                ]
            ],
            'actionAddExistsUser' => [
                [
                    'sender' => 1,
                    'amount' => 3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Add->value,
                ]
            ],
            'actionTransfer' => [
                [
                    'sender' => 1,
                    'recipient' => 2,
                    'amount' => 3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Transfer->value,
                ]
            ],
        ];
    }

    public static function providerCasesFailed(): array
    {
        return [
            'identifierIsRequired' => [
                [
                    'sender' => 1,
                    'amount' => 3.0,
                    'action' => ActionsEnum::Add->value,
                ]
            ],
            'senderNotExists' => [
                [
                    'sender' => 11,
                    'amount' => 3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Subtract->value,
                ]
            ],
            'recipientNotExists' => [
                [
                    'sender' => 1,
                    'amount' => 3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Transfer->value,
                ]
            ],
            'negativeAmount' => [
                [
                    'sender' => 1,
                    'recipient' => 2,
                    'amount' => -3.0,
                    'identifier' => uniqid(),
                    'action' => ActionsEnum::Transfer->value,
                ]
            ],
            'unknownAction' => [
                [
                    'sender' => 1,
                    'recipient' => 2,
                    'amount' => -3.0,
                    'identifier' => uniqid(),
                    'action' => 'ActionsEnum',
                ]
            ],
        ];
    }
}
