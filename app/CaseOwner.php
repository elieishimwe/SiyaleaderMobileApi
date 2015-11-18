<?php namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CaseOwner extends Eloquent
{


    protected $table    = 'caseOwners';
    protected $fillable = ['caseId','user','type','active'];

}
