<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WalletController extends Controller
{
    public function index()
    {
        // Get all wallets associated with the authenticated user
        $wallets = Wallet::where('user_id', Auth::id())->get();
        $totalBalance = 0;
        $primaryCurrency = 'EUR'; // Set your primary currency here

        // Loop through each wallet and convert balances as necessary
        foreach ($wallets as $wallet) {
            // Convert balance to primary currency
            $convertedBalance = convertCurrency($wallet->balance, $wallet->currency, $primaryCurrency);

            // Sum the converted balances
            $totalBalance += $convertedBalance;
        }

        // Prepare the response data
        return response()->json([
            'wallets' => $wallets,
            'total_balance' => number_format($totalBalance, 2), // Ensure proper formatting to 2 decimal places
            'currency' => $primaryCurrency,
        ]);
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'currency' => 'required|string|size:3',
        ]);
    
        try {
            // Add the authenticated user's ID to the wallet data
            $validated['user_id'] = Auth::id();
    
            // Create the wallet
            $wallet = Wallet::create($validated);
    
            // Return a JSON response with the created wallet and a 201 status
            return response()->json($wallet, 201);
    
        } catch (QueryException $e) {
            // Handle the case where a wallet with the same name already exists
            return response()->json(['error' => 'A wallet with this name already exists'], 409);
        }
    }
    
    
    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        // Ensure the wallet belongs to the authenticated user
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Загрузите транзакции вместе с категориями
        $wallet->load('transactions.category');
    
        // Преобразуем данные в удобный формат для ответа
        $walletData = [
            'id' => $wallet->id,
            'name' => $wallet->name,
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'transactions' => $wallet->transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'description' => $transaction->description,
                    'date' => $transaction->date,
                    'category_name' => $transaction->category->name ?? 'No Category',
                ];
            }),
        ];
    
        // Возвращаем JSON ответ с кошельком и его транзакциями
        return response()->json($walletData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
{
    // Ensure the wallet belongs to the authenticated user
    if ($wallet->user_id !== Auth::id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Validate the incoming request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'balance' => 'required|numeric',
        'currency' => 'required|string|size:3',
    ]);

    // Update the wallet
    $wallet->update($validated);

    // Get all wallets associated with the authenticated user
    $wallets = Wallet::where('user_id', Auth::id())->get();

    // Calculate the total balance for all wallets
    $totalBalance = $wallets->sum('balance');

    // Return all wallets and the total balance in a JSON response
    return response()->json([
        'wallets' => $wallets,
        'total_balance' => $totalBalance,
    ]);
}

    
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        // Ensure the wallet belongs to the authenticated user
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Delete the wallet
        $wallet->delete();
    
        // Get all wallets associated with the authenticated user
        $wallets = Wallet::where('user_id', Auth::id())->get();
    
        // Calculate the total balance for all wallets
        $totalBalance = $wallets->sum('balance');
    
        // Return all wallets and the total balance in a JSON response
        return response()->json([
            'wallets' => $wallets,
            'total_balance' => $totalBalance,
        ]);
    }
    
}
