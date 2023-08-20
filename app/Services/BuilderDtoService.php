<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OperationDto;
use App\Enums\ActionsEnum;
use App\Interfaces\BuilderDtoServiceInterface;
use App\Models\Balance;

class BuilderDtoService implements BuilderDtoServiceInterface
{
    private array $errors = [];

    public function create(array $data): ?OperationDto
    {
        $this->validate($data);
        if ($this->errors) {
            return null;
        }

        return new OperationDto(
            ActionsEnum::from($data['action']),
            $data['identifier'],
            (int) $data['sender'],
            ($data['recipient'] ?? null) ? (int) $data['recipient'] : null,
            (float) $data['amount'],
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validate(array $data): void
    {
        if (! isset($data['identifier'])) {
            $this->errors[] = 'identifier is required';
        }

        $amount = $data['amount'] ?? 0;
        if ($amount <= 0) {
            $this->errors[] = 'amount must be greater than zero';
        }

        $action = $data['action'] ?? '';
        if (! in_array($action, ActionsEnum::getValues())) {
            $this->errors[] = 'Unknown action';
        }

        $senderId = $data['sender'] ?? null;
        if (! is_numeric($senderId)) {
            $this->errors[] = 'sender id not numeric';
        }
        if ($action !== ActionsEnum::Add->value && ! Balance::existsAccount($senderId)) {
            $this->errors[] = 'sender not exists';
        }

        if ($action === ActionsEnum::Transfer->value) {
            $recipientId = $data['recipient'] ?? null;
            if (! is_numeric($recipientId)) {
                $this->errors[] = 'recipient id not numeric';
            }
            if (! Balance::existsAccount($recipientId)) {
                $this->errors[] = 'recipient not exists';
            }
        }
    }
}
