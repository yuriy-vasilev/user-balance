<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\OperationDto;
use App\Enums\ActionsEnum;
use App\Enums\TransactionStatusesEnum;
use App\Exceptions\InsufficientBalance;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\DataProviders\TransactionServiceDataProvider;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use TransactionServiceDataProvider;

    private TransactionServiceInterface $transactionService;
    private int $senderId = 1;
    private int $recipientId = 2;

    public function testAddNewUserBalance(): void
    {

        $op = $this->getOperationDto(ActionsEnum::Add, 100, uniqid(), 99);

        $this->transactionService->exec($op);

        $this->assertAccountBalance(99, 100, 100);
        $this->assertTransactionHas($op);
    }

    /**
     * @dataProvider providerUpdateBalance
     * @group smoke
     */
    public function testUpdateBalance(ActionsEnum $action, float $amount, array $expected): void
    {
        $op = $this->getOperationDto($action, $amount);

        $this->transactionService->exec($op);

        $this->assertAccountBalance($this->senderId, $expected['balance'], $expected['availableBalance']);
        $this->assertTransactionHas($op);
    }

    /**
     * @dataProvider providerUpdateBalanceFailed
     */
    public function testUpdateBalanceFailed(ActionsEnum $action, float $amount, $userId): void
    {
        $this->expectException(ModelNotFoundException::class);

        $op = $this->getOperationDto($action, $amount, null, $userId);

        $this->transactionService->exec($op);
    }

    public function testSubtractInsufficientBalance(): void
    {
        $this->expectException(InsufficientBalance::class);

        $op = $this->getOperationDto(ActionsEnum::Subtract, 200);

        $this->transactionService->exec($op);
    }

    public function testSubtractDouble(): void
    {
        $this->expectException(QueryException::class);

        $op = $this->getOperationDto(ActionsEnum::Subtract, 20);

        $this->transactionService->exec($op);
        $this->transactionService->exec($op);
    }

    /**
     * @group smoke
     */
    public function testTransfer(): void
    {
        $op = $this->getOperationDto(ActionsEnum::Transfer, 20);

        $this->transactionService->exec($op);

        $this->assertAccountBalance($this->senderId, 80, 80);
        $this->assertAccountBalance($this->recipientId, 120, 120);
        $this->assertTransactionHas($op);
        $this->assertTransactionHas($op, TransactionStatusesEnum::Completed, null, $op->recipient);
    }

    public function testTransferFailed(): void
    {
        $this->expectException(InsufficientBalance::class);

        $op = $this->getOperationDto(ActionsEnum::Transfer, 200);

        $this->transactionService->exec($op);
    }

    public function testFreeze(): void
    {
        $op = $this->getOperationDto(ActionsEnum::Freeze, 20);

        $this->transactionService->exec($op);

        $this->assertAccountBalance($this->senderId, 100, 80);
        $this->assertTransactionHas($op, TransactionStatusesEnum::Frozen);
    }

    public function testFreezeFailed(): void
    {
        $this->expectException(InsufficientBalance::class);

        $op = $this->getOperationDto(ActionsEnum::Freeze, 200);

        $this->transactionService->exec($op);
    }

    public function testApprove(): void
    {
        $op = $this->getOperationDto(ActionsEnum::Freeze, 20);
        $this->transactionService->exec($op);

        $op = $this->getOperationDto(ActionsEnum::Approve, $op->amount, $op->identifier);
        $this->transactionService->exec($op);

        $this->assertAccountBalance($this->senderId, 80, 80);
        $this->assertTransactionHas($op, TransactionStatusesEnum::Completed, ActionsEnum::Subtract);
    }

    public function testApproveFailed(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $op = $this->getOperationDto(ActionsEnum::Approve, 20);

        $this->transactionService->exec($op);
    }

    public function testReject(): void
    {
        $op = $this->getOperationDto(ActionsEnum::Freeze, 20);
        $this->transactionService->exec($op);

        $op = $this->getOperationDto(ActionsEnum::Reject, $op->amount, $op->identifier);
        $this->transactionService->exec($op);

        $this->assertAccountBalance($this->senderId, 100, 100);
        $this->assertTransactionHas($op, TransactionStatusesEnum::Rejected, ActionsEnum::Freeze);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionService = $this->app->make(TransactionServiceInterface::class);
        Balance::factory(['user_id' => $this->senderId])->create();
        Balance::factory(['user_id' => $this->recipientId])->create();
    }

    private function getOperationDto(
        ActionsEnum $action,
        float $amount,
        ?string $identifier = null,
        ?int $userId = null,
    ): OperationDto
    {
        return new OperationDto(
            $action,
            $identifier ?: uniqid(),
            $userId ?: $this->senderId,
            $this->recipientId,
            $amount,
        );
    }

    private function assertAccountBalance(int $userId, float $balance, float $availableBalance): void
    {
        $accountSender = Balance::getByUserId($userId);
        $this->assertEquals($balance, $accountSender->balance);
        $this->assertEquals($availableBalance, $accountSender->availableBalance);
    }

    private function assertTransactionHas(
        OperationDto $op,
        TransactionStatusesEnum $status = TransactionStatusesEnum::Completed,
        ?ActionsEnum $action = null,
        ?int $userId = null,
    ): void
    {
        $this->assertDatabaseHas(Transaction::getTableName(), [
            'identifier' => $op->identifier,
            'user_id' => $userId ?: $op->sender,
            'action' => $action ? $action->value : $op->action->value,
            'status' => $status->value,
        ]);
    }
}
