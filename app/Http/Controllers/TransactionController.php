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
        $transactions = Transaction::where('user_id', Auth::id())->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Find the category by name or create it if it doesn't exist
        $category = Category::firstOrCreate(
            [
                'name' => $request->category_name,
                'user_id' => Auth::id(),
                // Associate the category with the current user
            ]
        );

        // Get the selected wallet
        $wallet = Wallet::find($request->wallet_id);

        // Check if it's an expense or income (assuming negative amount for expenses)
        $isExpense = $request->amount < 0;
        
        // Adjust the wallet balance
        if ($isExpense) {
            if ($wallet->balance >= abs($request->amount)) {
                // Deduct the amount for an expense
                $wallet->balance -= abs($request->amount);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient funds in the selected wallet.',
                ], 400); // Return error response for insufficient funds
            }
        } else {
            // Add the amount for income
            $wallet->balance += $request->amount;
        }

        $wallet->save();  // Save the updated wallet balance

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'category_id' => $category->id,
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'description' => $request->description, 
            'date' => $request->date,
            'currency' => $wallet->currency,
        ]);

        // Get all wallets for the authorized user
        $wallets = Wallet::where('user_id', Auth::id())->get();

        // Return a response with an array of wallets and the category name
        return response()->json([
            'success' => true,
            'message' => 'Transaction created succesfully.',
            'transaction' => [
                'amount' => $transaction->amount,
                'category_name' => $category->name,  // Вернуть имя категории
                'wallet_id' => $transaction->wallet_id,
                'currency' => $transaction->currency,
            ],
            'wallets' => $wallets,  // Вернуть массив всех кошельков
        ], 201);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $wallet = Wallet::find($request->wallet_id);

        // Revert old transaction's effect on wallet
        if ($transaction->amount < 0) {
            $wallet->balance += abs($transaction->amount);  // Undo previous expense
        } else {
            $wallet->balance -= $transaction->amount;  // Undo previous income
        }
    
        // Apply the new transaction amount to wallet
        if ($request->amount < 0) {
            if ($wallet->balance >= abs($request->amount)) {
                $wallet->balance -= abs($request->amount);  // Deduct for new expense
            } else {
                return redirect()->back()->withErrors(['Insufficient funds in the selected wallet.']);
            }
        } else {
            $wallet->balance += $request->amount;  // Add for new income
        }
    
        $wallet->save();

        $transaction->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
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

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}