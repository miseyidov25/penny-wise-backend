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
    
        $wallet = Wallet::create($validated);
    
        return response()->json($wallet, 201);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        return response()->json($wallet);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric'
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
