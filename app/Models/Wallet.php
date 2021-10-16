<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;


    protected $fillable = [
        'wallet_number', 'account_id',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
