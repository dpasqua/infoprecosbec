<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UGE extends Model
{
    protected $table = 'uges';

    public function gestao()
    {
        return $this->belongsTo(Gestao::class,'id_gestao');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }

    public function Orgao()
    {
        return $this->belongsTo(Orgao::class, 'id_orgao');
    }

}