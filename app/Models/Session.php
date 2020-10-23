<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class Session extends Model
{
    use HasFactory,softDeletes;

    protected $fillable=[
        "at_session_id",
        "session_level",
        "phone_number"
    ];
    protected $casts=[
        "at_session_id"=>"string",
        "session_level"=>"tinyInteger",
        "phone_number" =>"string"
    ];
    public function user(){
        return $this->belongsTo('App\Model\User');
    }
}
