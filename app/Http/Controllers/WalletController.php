<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the wallets associated with the authenticated user
        $wallets = Wallet::where('user_id', Auth::id())->get(); 
        $totalBalance = $wallets->sum('balance');
    
        return response()->json([
            'wallets' => $wallets,
            'total_balance' => $totalBalance
        ]);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'currency' => 'required|string|size:3'
        ]);
        
        try {
            $wallet = Wallet::create($validated);
            return response()->json(['message' => 'Wallet created successfully']);
            return response()->json($wallet, 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'A wallet with this name already exists'], 409);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
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
                    'description' => $transaction->description,
                    'date' => $transaction->date,
                    'category_name' => $transaction->category->name ?? 'No Category',
                    'currency' => $transaction->currency,
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:wallets,name,' . $wallet->id,
            'balance' => 'required|numeric',
            'currency' => 'required|string|size:3'
        ], [
            'name.unique' => 'A wallet with this name already exists.',
        ]);
    
        $wallet->update($validated);
    
        return response()->json($wallet);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
    
        return response()->json(['message' => 'Wallet deleted successfully.']);
    }
    
}
