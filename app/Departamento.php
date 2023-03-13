<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'departamento';

    protected $fillable = [
        'nome'
    ];

}
