<?php namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CaseResponder extends Eloquent
{


    protected $table    = 'responders';
    protected $fillable = ['department','category','sub_category','sub_sub_category','firstResponder','secondResponder','thirdResponder','active'];



}
