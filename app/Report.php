<?php namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Report extends Eloquent
{

    protected $table    = 'ccg_rap';
    protected $fillable = ['prob_category','status','prob_exp','gps_lat','gps_lng','ID','GPS'];
}
