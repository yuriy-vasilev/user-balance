<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public readonly Transaction $transaction)
    {
    }
}
