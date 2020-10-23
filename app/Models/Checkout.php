<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\softDeletes;

class Checkout extends Model
{
    use HasFactory;

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
