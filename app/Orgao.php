<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orgao extends Model
{
    protected $table = 'orgaos';

    public function uge()
    {
        return $this->hasMany(UGE::class, 'id_orgao');
    }

}