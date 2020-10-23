<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

    use HasFactory,softDeletes;

    protected $fillable=[
        "phone_number",
        "transactions",
        "savings",
        "loan",
        "lock_savings",
        "lock_savings_target",
        "loan_limit",
        "loan_taken_on",
        "loan_due_date",
        "lock_savings_maturity_date"
    ];

    protected $casts=[
        "phone_number"              =>"string",
        "transactions"              =>"string",
        "savings"                   =>"double",
        "loan"                      =>"double",
        "lock_savings"              =>"double",
        "lock_savings_target"       =>"double",
        "loan_limit"                =>"double",
        "loan_taken_on"             =>"datetime",
        "loan_due_date"             =>"datetime",
        "lock_savings_maturity_date"=>"datetime"
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }


}
