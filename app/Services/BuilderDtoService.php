<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OperationDto;
use App\Enums\ActionsEnum;
use App\Interfaces\BuilderDtoServiceInterface;
use App\Models\Balance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class BuilderDtoService implements BuilderDtoServiceInterface
{
    private array $errors = [];

    public function create(array $data): ?OperationDto
    {
        $validator = Validator::make($data, $this->getRules($data));
        if ($validator->fails()) {
            $this->errors = $validator->errors()->toArray();

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

    private function getRules(array $data): array
    {
        $action = $data['action'] ?? null;
        $senderRules = ['required', 'numeric'];
        if ($action !== ActionsEnum::Add->value) {
            $senderRules[] = 'exists:' . Balance::getTableName() . ',user_id';
        }

        $rules = [
            'identifier' => ['required'],
            'amount' => ['required', 'numeric', 'gte:0'],
            'action' => [
                'required',
                new Enum(ActionsEnum::class),
            ],
            'sender' => $senderRules,
        ];

        if ($action === ActionsEnum::Transfer->value) {
            $rules['recipient'] = [
                'required',
                'numeric',
                'exists:' . Balance::getTableName() . ',user_id',
            ];
        }

        return $rules;
    }
}
