<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaystackTransaction extends Model {
    protected $fillable = ['user_id','reference','amount','currency','status','gateway_response','channel','paid_at'];
    public function user() { return $this->belongsTo(User::class); }
}
