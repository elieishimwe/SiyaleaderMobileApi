<?php namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserNew extends Eloquent
{

    protected $table    = 'users';
    protected $fillable = ['name', 'email', 'password','surname','role','cellphone','username','api_key','department'];
}
