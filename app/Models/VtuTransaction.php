<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VtuTransaction extends Model {
    protected $fillable = ['user_id','reference','service_type','network','phone','plan_name','plan_code','amount','profit','api_response','status','provider'];
    public function user() { return $this->belongsTo(User::class); }
}
