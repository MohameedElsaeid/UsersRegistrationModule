<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable ,SoftDeletes;
    
    
    
    /**
     * Get existing or make new access token
     */
    public function makeApiToken()
    {
        return $this->createToken('user')->accessToken;
    }
    
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function createValidation(){
        return [
            'first_name'              => 'required|string|min:3|max:20',
            'last_name'              => 'required|string|min:3|max:20',
            'country_code'      => 'required|string|max:191',
            'mobile'            => 'required|numeric|unique:users,mobile',
            'password'          => 'required|string|regex:/^\S*$/u|min:8',
        ];
    }
    
    public function updateValidation(){
        return [
            'first_name'              => 'required|string|min:3|max:20',
            'last_name'              => 'required|string|min:3|max:20',
            'country_code'      => 'required|string|max:191',
            'mobile'            => 'required|numeric',
            'password'          => 'required|string|regex:/^\S*$/u|min:8',
        ];
    }
    
    public function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }

    public function devices(){
        return $this->hasMany(Device::class);
    }

}
