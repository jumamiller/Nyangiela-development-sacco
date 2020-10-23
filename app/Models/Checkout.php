<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;
use App\Models\User;

class Checkout extends Model
{
    use HasFactory,softDeletes;

    protected $fillable=[
        "status",
        "phone_number",
        "amount"
    ];

    protected $casts=[
        "status"        =>"boolean",
        "phone_number"  =>"string",
        "amount"        =>"double"
    ];
    public function user(){
        return $this->belongsTo('User');
    }
}
