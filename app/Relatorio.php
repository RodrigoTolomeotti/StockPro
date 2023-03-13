<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Relatorio extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = '';

    protected $fillable = [];

    public function envio() {
        return $this->belongsToMany('App\Envio');
    }

    public function Retorno() {
        return $this->belongsToMany('App\Retorno');
    }

}
