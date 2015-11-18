<?php namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;
class User extends Eloquent
{
    protected $table = 'imb_oss_users';
    protected $fillable = ['Sname', 'Fname', 'Cell1','star_user','api_user','Password','Cell1Type'];
}
