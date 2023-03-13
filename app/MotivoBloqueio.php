<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MotivoBloqueio extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'motivo_bloqueio';

    protected $fillable = [
        'descricao',
        'dica'
    ];
}
