<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Dto\OperationDto;

interface BuilderDtoServiceInterface
{
    public function create(array $data): ?OperationDto;

    public function getErrors(): array;
}
