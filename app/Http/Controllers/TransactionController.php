<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Handle a transaction success event.
     */
    public function handleTransactionSuccess(Request $request): JsonResponse
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            // Calculate the profit (3% commission rate)
            $profit = Transaction::calculateProfit($validatedData['amount']);

            // Create and save the transaction
            $transaction = Transaction::create([
                'amount' => $validatedData['amount'],
                'profit' => $profit,
            ]);

            return response()->json([
                'message' => 'Transaction processed successfully.',
                'transaction_id' => $transaction->id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Transaction processing failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Transaction processing failed.',
            ], 500);
        }
    }
}
