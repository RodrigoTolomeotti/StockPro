<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profissao extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'profissao';

    protected $fillable = [
        'nome'
    ];

}
