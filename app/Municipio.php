<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipios';

    public function uge()
    {
        return $this->hasMany(UGE::class, 'id_municipio');
    }

}