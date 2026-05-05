<?php
namespace App\Services;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
class WalletService {
    public function credit(User $user, $amount, $desc='Credit') {
        $before = $user->wallet_balance;
        $after = $before + $amount;
        $user->update(['wallet_balance' => $after]);
        WalletTransaction::create([
            'user_id' => $user->id,
            'reference' => 'WAL-'.Str::random(16),
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $desc,
            'status' => 'success'
        ]);
        return true;
    }
    public function debit(User $user, $amount, $desc='Debit') {
        $before = $user->wallet_balance;
        if ($before < $amount) return false;
        $after = $before - $amount;
        $user->update(['wallet_balance' => $after]);
        WalletTransaction::create([
            'user_id' => $user->id,
            'reference' => 'WAL-'.Str::random(16),
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $desc,
            'status' => 'success'
        ]);
        return true;
    }
}
