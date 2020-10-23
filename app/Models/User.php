<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Checkout;
use App\Models\Account;

class User extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'phone_number',
        'PIN',
    ];
    /** The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'PIN',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'string',
        'last_name' =>'string',
        'phone_number'=>'string',
        'PIN'       =>'string',
        'username'  =>'string',
    ];

    /**
     * define relationships
     */
    public function checkout(){
        return $this->hasOne('Checkout');
    }
    public function account(){
        return $this->hasOne('Account');
    }


}
