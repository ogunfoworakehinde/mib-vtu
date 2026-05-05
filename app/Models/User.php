<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable {
    protected $fillable = [
        'full_name','email','phone','password','role','theme',
        'wallet_balance','commission_balance','status'
    ];
    protected $hidden = ['password','remember_token'];
}
