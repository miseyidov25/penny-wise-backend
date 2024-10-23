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
            'wallet_id' => 'required|exists:wallets,id', // Keep wallet_id
            'category_name' => 'required|string|max:255', // Use category_name
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Ensure the wallet belongs to the authenticated user
        $wallet = Wallet::where('id', $validated['wallet_id'])
                        ->where('user_id', Auth::id())
                        ->firstOrFail(); // This will throw a 404 if the wallet doesn't belong to the user

        // Ensure the category exists for the authenticated user
        $category = Category::where('name', $validated['category_name'])
                            ->where('user_id', Auth::id())
                            ->first(); // Find the category by name

        if (!$category) {
            // Optionally, you can create the category if it doesn't exist
            $category = Category::create([
                'name' => $validated['category_name'],
                'user_id' => Auth::id(),
            ]);
        }

        // Check if it's an expense or income (assuming negative amount for expenses)
        $isExpense = $validated['amount'] < 0;

        // Adjust the wallet balance
        if ($isExpense) {
            // Deduct the amount for an expense
            if ($wallet->balance >= abs($validated['amount'])) {
                $wallet->balance -= abs($validated['amount']);
            } else {
                return response()->json(['error' => 'Insufficient funds in the selected wallet.'], 403);
            }
        } else {
            // Add the amount for income
            $wallet->balance += $validated['amount'];
        }

        $wallet->save(); // Save the updated wallet balance

        // Create the transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'category_id' => $category->id, // Use the category ID for the transaction
            'wallet_id' => $wallet->id, // Keep the wallet ID
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'currency' => $wallet->currency,
        ]);

        // Get all wallets for the authorized user
        $wallets = Wallet::where('user_id', Auth::id())->get();

        // Return a response with an array of wallets and the category name
        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully.',
            'transaction' => [
                'amount' => $transaction->amount,
                'category_name' => $category->name, // Return the category name
                'wallet_id' => $transaction->wallet_id,
                'currency' => $transaction->currency,
            ],
            'wallets' => $wallets, // Return array of all wallets
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