<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $guarded = [];
    
    public function createValidation(){
        return [
            'user_id'              => 'required|numeric',
            'device_id'              => 'required|numeric',
            'device_model'      => 'required|string|max:191',
            'version'            => 'required|string',
            'platform'          => 'required|string',
            'firebase_token'          => 'required|string',
            'online'          => 'required|in:0,1',
        ];
    }
    
    public function updateValidation(){
        return [
            'user_id'              => 'required|numeric',
            'device_id'              => 'required|numeric',
            'device_model'      => 'required|string|max:191',
            'version'            => 'required|string',
            'platform'          => 'required|string',
            'firebase_token'          => 'required|string',
            'online'          => 'required|in:0,1',
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
