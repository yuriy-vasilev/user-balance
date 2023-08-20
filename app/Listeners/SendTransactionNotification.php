<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class SendTransactionNotification implements ShouldQueue
{
    public ?string $queue = 'transactions';

    public function __construct()
    {
    }

    public function handle(object $event): void
    {
    }
}
