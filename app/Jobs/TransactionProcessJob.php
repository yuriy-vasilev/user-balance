<?php

namespace App\Jobs;

use App\Exceptions\InsufficientBalance;
use App\Interfaces\BuilderDtoServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const DELAY_RETRYING = 1;

    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $payload
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(
        BuilderDtoServiceInterface $builderDtoService,
        TransactionServiceInterface $transactionService,
    ): void {
        Log::debug('[TransactionProcessJob] Start', ['payload' => $this->payload]);

        $op = $builderDtoService->create($this->payload);
        if (! $op) {
            Log::error('[TransactionProcessJob] Incorrect operation', [
                'errors' => $builderDtoService->getErrors(),
                'payload' => $this->payload,
            ]);

            return;
        }

        try {
            $transaction = Transaction::getCompleted($op->identifier, $op->sender);
            if ($transaction) {
                Log::info(
                    '[TransactionProcessJob] Skip operation, transaction has been completed',
                    ['payload' => $this->payload]
                );

                return;
            }

            $transactionService->exec($op);
        } catch (InsufficientBalance | ModelNotFoundException $exception) {
            Log::warning('[TransactionProcessJob] Skip operation', [
                'errorMessage' => $exception->getMessage(),
                'payload' => $this->payload,
            ]);

            return;
        } catch (Throwable $exception) {
            Log::error('[TransactionProcessJob] Error', [
                'errorMessage' => $exception->getMessage(),
                'payload' => $this->payload,
            ]);

            $this->release(self::DELAY_RETRYING);
        }
    }
}
