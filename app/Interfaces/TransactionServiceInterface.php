<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Dto\OperationDto;

interface TransactionServiceInterface
{
    public function exec(OperationDto $op): void;
}
