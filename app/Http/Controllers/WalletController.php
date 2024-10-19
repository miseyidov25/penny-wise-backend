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
        $wallets = Wallet::all();
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
            'balance' => 'required|numeric'
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
        // Download related transactions for wallet
        $wallet->load('transactions');

        // Return a JSON response with the wallet and its transactions
        return response()->json($wallet);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:wallets,name,' . $wallet->id,
            'balance' => 'required|numeric',
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
