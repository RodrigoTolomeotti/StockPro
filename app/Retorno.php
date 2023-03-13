<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Retorno extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'retorno';

    protected $fillable = [
        'envio_id',
        'mensagem',
        'origem_id',
        'ind_avaliacao',
        'data_avaliacao',
        'data_criacao',
        'data_atualizacao'
    ];

}
