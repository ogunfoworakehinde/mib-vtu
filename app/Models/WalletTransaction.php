<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WalletTransaction extends Model {
    protected $fillable = ['user_id','reference','type','amount','balance_before','balance_after','description','status'];
    public function user() { return $this->belongsTo(User::class); }
}
