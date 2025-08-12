<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class AccountController extends Controller
{
    private $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    public function reset()
    {
        Redis::flushall();
        return response('OK', 200);
    }
    
    public function balance(Request $request): Response
    {
        $accountId = $request->query('account_id');
        $balance = Redis::get("account:{$accountId}");

        if ($balance !== null) {
            return response($balance, 200);
        }
        
        return response(0, 404);
    }

    public function event(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|string|in:deposit,withdraw,transfer',
            'amount'      => 'required|numeric',
            'destination' => 'sometimes|required_if:type,deposit,transfer|string',
            'origin'      => 'sometimes|required_if:type,withdraw,transfer|string',
        ]);

        switch ($data['type']) {
            case 'deposit':
                return $this->eventService->handleDeposit($data);
            case 'withdraw':
                return $this->eventService->handleWithdraw($data);
            case 'transfer':
                return $this->eventService->handleTransfer($data);
        }

        return response()->json(0, 400);
    }

}
