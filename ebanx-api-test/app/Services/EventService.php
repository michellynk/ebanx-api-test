<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class EventService
{
    public function handleDeposit(array $data): JsonResponse
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

    public function handleWithdraw(array $data): JsonResponse
    {
        $accountId = $data['origin'];
        $amount = $data['amount'];
        $balance = Redis::get("account:{$accountId}");

        if (!Redis::exists("account:{$accountId}")) {
            return response()->json(0, 404);
        }

        if ($balance < $amount) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        $newBalance = $balance - $amount;
        Redis::set("account:{$accountId}", $newBalance);

        return response()->json(['origin' => [
            'id' => $accountId,
            'balance'=> $newBalance
        ]], 201);
    }


    public function handleTransfer(array $data): JsonResponse
    {
        $accountId = $data['origin'];
        $destinationId = $data['destination'];
        $amount = $data['amount'];

        $originBalance = Redis::get("account:{$accountId}");
        $destinationBalance = Redis::exists("account:{$destinationId}") ? Redis::get("account:{$destinationId}") : 0;

        if (!Redis::exists("account:{$accountId}")) {
            return response()->json(0, 404);
        }

        if ($originBalance < $amount) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        $newOriginBalance = $originBalance - $amount;
        $newDestinationBalance = $destinationBalance + $amount;

        Redis::set("account:{$accountId}", $newOriginBalance);
        Redis::set("account:{$destinationId}", $newDestinationBalance);

        return response()->json([
            'origin' => [
                'id' => $accountId,
                'balance'=> $newOriginBalance
            ],
            'destination' => [
                'id' => $destinationId,
                'balance'=> $newDestinationBalance
            ],
        ], 201);
    }
}