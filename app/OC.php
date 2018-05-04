<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OC extends Model
{
    protected $table = 'ocs';

    protected $dates = ['dt_encerramento'];

    public function uge()
    {
        return $this->belongsTo(UGE::class, 'id_uge');
    }

}