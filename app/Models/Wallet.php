<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['name', 'balance', 'currency'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
