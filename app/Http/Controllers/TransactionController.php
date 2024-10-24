<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('category')->where('user_id', Auth::id())->get();

        return response()->json($transactions->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->date,
                'category_name' => $transaction->category ? $transaction->category->name : null, // Получаем имя категории
            ];
        }));
    }

    public function store(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'wallet_id' => 'required|exists:wallets,id',
        'category_name' => 'required|string|max:255', // Validate category_name
        'amount' => 'required|numeric',
        'description' => 'nullable|string',
        'date' => 'required|date',
    ]);

    // Ensure the wallet belongs to the authenticated user
    $wallet = Wallet::where('id', $validated['wallet_id'])
                    ->where('user_id', Auth::id())
                    ->first();

    // Check if the wallet exists and belongs to the user
    if (!$wallet) {
        return response()->json(['error' => 'Unauthorized: Wallet does not belong to the authenticated user.'], 403);
    }

    // Ensure the category belongs to the authenticated user or create it if it doesn't exist
    $category = Category::firstOrCreate(
        [
            'name' => $validated['category_name'],
            'user_id' => Auth::id(), // Associate the category with the current user
        ]
    );

    // Check if it's an expense or income (assuming negative amount for expenses)
    $isExpense = $validated['amount'] < 0;

    // Adjust the wallet balance
    if ($isExpense) {
        // Deduct the amount for an expense
        $wallet->balance -= abs($validated['amount']);
    } else {
        // Add the amount for income
        $wallet->balance += $validated['amount'];
    }

    $wallet->save(); // Save the updated wallet balance

    // Create the transaction
    $transaction = Transaction::create([
        'user_id' => Auth::id(),
        'category_id' => $category->id, // Use the category_id from firstOrCreate
        'wallet_id' => $wallet->id,
        'amount' => $validated['amount'],
        'description' => $validated['description'],
        'date' => $validated['date'],
        'currency' => $wallet->currency,
    ]);

    // Load the wallet's transactions (eager load)
    $wallet->load('transactions');

    // Return the wallet with its transactions
    return response()->json([
        'success' => true,
        'message' => 'Transaction created successfully.',
        'wallet' => $wallet, // Return the wallet
    ], 201);
}
    




    public function update(Request $request, Transaction $transaction)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Find the wallet based on the request
        $wallet = Wallet::findOrFail($validatedData['wallet_id']);

        // Revert the old transaction's effect on the wallet
        if ($transaction->amount < 0) {
            $wallet->balance += abs($transaction->amount);  // Undo previous expense
        } else {
            $wallet->balance -= $transaction->amount;  // Undo previous income
        }

        // Check if the new transaction amount is an expense
        if ($validatedData['amount'] < 0) {
            if ($wallet->balance >= abs($validatedData['amount'])) {
                $wallet->balance += $validatedData['amount'];  // Deduct for new expense
            } else {
                return response()->json(['error' => 'Insufficient funds in the selected wallet.'], 400);
            }
        } else {
            $wallet->balance += $validatedData['amount'];  // Add for new income
        }

        // Save the updated wallet balance
        $wallet->save();

        // Update the transaction with the new data
        $transaction->update([
            'category_id' => $validatedData['category_id'],
            'wallet_id' => $validatedData['wallet_id'], // Use the validated wallet ID
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'],
            'date' => $validatedData['date'],
        ]);

        // Get the linked wallet for response
        $linkedWallet = Wallet::find($transaction->wallet_id);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => [
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->date,
                'category_name' => $transaction->category->name, // Ensure category relationship is loaded
                'wallet' => $linkedWallet,
            ],
        ]);
    }


    public function destroy(Transaction $transaction)
    {
        // Adjust the wallet balance when a transaction is deleted
        $wallet = Wallet::find($transaction->wallet_id);

        if ($transaction->amount < 0) {
            $wallet->balance += abs($transaction->amount);  // Undo expense
        } else {
            $wallet->balance -= $transaction->amount;  // Undo income
        }

        $wallet->save();

        // Delete the transaction
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully.',
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'date' => $transaction->date,
            'category_name' => $transaction->category->name,
            'wallet' => $wallet,
        ]);
    }
}