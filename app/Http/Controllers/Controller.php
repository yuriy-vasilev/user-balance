<?php

namespace App\Http\Controllers;

use App\Jobs\TransactionProcessJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Queue;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request): void
    {
        Queue::push(new TransactionProcessJob($request->all()));
    }
}
