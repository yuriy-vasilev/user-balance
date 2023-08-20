<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OperationDto;
use App\Enums\ActionsEnum;
use App\Enums\TransactionStatusesEnum;
use App\Events\TransactionSuccess;
use App\Exceptions\InsufficientBalance;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionService implements TransactionServiceInterface
{
    /**
     * @throws Throwable
     */
    public function exec(OperationDto $op): void
    {
        $transaction = null;
        try {
            DB::beginTransaction();

            match ($op->action) {
                ActionsEnum::Add => $transaction = $this->add($op),
                ActionsEnum::Subtract => $transaction = $this->subtract($op),
                ActionsEnum::Transfer => $transaction = $this->transfer($op),
                ActionsEnum::Freeze => $transaction = $this->freeze($op),
                ActionsEnum::Approve => $transaction = $this->approve($op),
                ActionsEnum::Reject => $transaction = $this->reject($op),
            };

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        $this->sendEvent($op, $transaction);
    }

    /**
     * @throws Throwable
     */
    private function add(OperationDto $op): Transaction
    {
        $accountSender = Balance::where('user_id', $op->sender)
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => $op->sender]);

        $accountSender->increment('balance', $op->amount);
        $accountSender->increment('available_balance', $op->amount);

        return $this->saveTransaction($op->sender, $op->amount, $op);
    }

    /**
     * @throws Throwable
     */
    private function subtract(OperationDto $op): Transaction
    {
        $accountSender = Balance::getByUserIdWithLock($op->sender);
        if ($op->amount > $accountSender->availableBalance) {
            throw new InsufficientBalance();
        }

        $accountSender->decrement('balance', $op->amount);
        $accountSender->decrement('available_balance', $op->amount);

        return $this->saveTransaction($op->sender, -1 * $op->amount, $op);
    }

    /**
     * @throws Throwable
     */
    private function transfer(OperationDto $op): Transaction
    {
        $accountSender = Balance::getByUserIdWithLock($op->sender);
        if ($op->amount > $accountSender->availableBalance) {
            throw new InsufficientBalance();
        }

        $accountSender->decrement('balance', $op->amount);
        $accountSender->decrement('available_balance', $op->amount);
        $this->saveTransaction($op->sender, -1 * $op->amount, $op);

        $accountRecipient = Balance::getByUserIdWithLock($op->recipient);
        $accountRecipient->increment('balance', $op->amount);
        $accountRecipient->increment('available_balance', $op->amount);

        return $this->saveTransaction($op->recipient, $op->amount, $op);
    }

    /**
     * @throws Throwable
     */
    private function freeze(OperationDto $op): Transaction
    {
        $accountSender = Balance::getByUserIdWithLock($op->sender);
        if ($op->amount > $accountSender->availableBalance) {
            throw new InsufficientBalance();
        }

        $accountSender->decrement('available_balance', $op->amount);

        return $this->saveTransaction(
            $op->sender,
            -1 * $op->amount,
            $op,
            TransactionStatusesEnum::Frozen,
        );
    }

    /**
     * @throws Throwable
     */
    private function approve(OperationDto $op): Transaction
    {
        $transaction = Transaction::getFrozenWithLock($op->identifier);
        $accountSender = Balance::getByUserIdWithLock($op->sender);
        if ($op->amount > $accountSender->balance) {
            throw new InsufficientBalance();
        }

        $accountSender->decrement('balance', $op->amount);
        $transaction->action = ActionsEnum::Subtract->value;
        $transaction->status = TransactionStatusesEnum::Completed->value;
        $transaction->save();

        return $transaction;
    }

    private function reject(OperationDto $op): Transaction
    {
        $transaction = Transaction::getFrozenWithLock($op->identifier);
        $accountSender = Balance::getByUserIdWithLock($op->sender);
        $accountSender->increment('available_balance', $op->amount);
        $transaction->status = TransactionStatusesEnum::Rejected->value;
        $transaction->save();

        return $transaction;
    }

    private function saveTransaction(
        int $userId,
        float $amount,
        OperationDto $op,
        TransactionStatusesEnum $status = TransactionStatusesEnum::Completed,
    ): Transaction {
        $transaction = new Transaction();
        $transaction->userId = $userId;
        $transaction->identifier = $op->identifier;
        $transaction->amount = $amount;
        $transaction->action = $op->action->value;
        $transaction->status = $status->value;
        $transaction->save();

        return $transaction;
    }

    private function sendEvent(
        OperationDto $op,
        ?Transaction $transaction
    ): void {
        if ($transaction) {
            event(new TransactionSuccess($transaction));
            if ($op->action === ActionsEnum::Transfer) {
                $rTransaction = Transaction::getCompleted(
                    $op->identifier,
                    $op->recipient,
                );
                if ($rTransaction) {
                    event(new TransactionSuccess($rTransaction));
                }
            }
        }
    }
}
