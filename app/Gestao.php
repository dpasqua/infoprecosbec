<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gestao extends Model
{
    protected $table = 'gestoes';

    public function uge()
    {
        return $this->hasMany(UGE::class, 'id_gestao');
    }
}