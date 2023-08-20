<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\ActionsEnum;

class OperationDto
{
    public function __construct(
        public readonly ActionsEnum $action,
        public readonly string $identifier,
        public readonly int $sender,
        public readonly ?int $recipient,
        public readonly float $amount,
    ) {
    }
}
