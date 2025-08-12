<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

class AccountController extends Controller
{
    private static array $accounts = [];

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
                return $this->handleDeposit($data);
            case 'withdraw':
                return $this->handleWithdraw($data);
            case 'transfer':
                return $this->handleTransfer($data);
        }

        return response()->json(0, 400);
    }

    private function handleDeposit(array $data): JsonResponse
    {
        $accountId = $data['destination'];
        $amount = $data['amount'];

        if (!Redis::exists("account:{$accountId}")) {
            Redis::set("account:{$accountId}", $amount);
        } else {
            Redis::incrby("account:{$accountId}", $amount);
        }

        return response()->json(['destination' => [
            'id' => $accountId,
            'balance' => (int) Redis::get("account:{$accountId}")
        ]], 201);
    }

    private function handleWithdraw(array $data): JsonResponse
    {
        $originId = $data['origin'];
        $amount = $data['amount'];

        if (!isset(self::$accounts[$originId])) {
            return response()->json(0, 404);
        }
        self::$accounts[$originId]['balance'] -= $amount;

        return response()->json(['origin' => self::$accounts[$originId]], 201);
    }

    private function handleTransfer(array $data): JsonResponse
    {
        $originId = $data['origin'];
        $destinationId = $data['destination'];
        $amount = $data['amount'];

        if (!isset(self::$accounts[$originId])) {
            return response()->json(0, 404);
        }

        if (!isset(self::$accounts[$destinationId])) {
            self::$accounts[$destinationId] = ['id' => $destinationId, 'balance' => 0];
        }

        self::$accounts[$originId]['balance'] -= $amount;
        self::$accounts[$destinationId]['balance'] += $amount;

        return response()->json([
            'origin' => self::$accounts[$originId],
            'destination' => self::$accounts[$destinationId],
        ], 201);
    }
}
