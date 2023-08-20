<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class InsufficientBalance extends Exception
{
    public function __construct($message = 'Insufficient balance', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
