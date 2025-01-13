<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\TransactionSuccessEvent;

class TransactionController extends Controller
{
    /**
     * Handle a transaction success event.
     */
    public function handleTransactionSuccess(Request $request): JsonResponse
    {
        $transactionData = $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        event(new TransactionSuccessEvent($transactionData));

        return response()->json(['message' => 'Transaction is being processed.'], 202);
    }
}
