<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
