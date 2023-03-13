<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bloqueio extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'bloqueio';

    protected $fillable = [
        'descricao'
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

    public function contato() {
        return $this->hasOne('App\Contato');
    }

    public function motivo_bloqueio() {
        return $this->hasOne('App\MotivoBloqueio');
    }

}
