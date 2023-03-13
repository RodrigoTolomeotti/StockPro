<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tela extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'tela';

    protected $fillable = [
        'nome',
        'url',
        'icone_classe',
        'menu_destino'
    ];

    public function niveis() {
        return $this->belongsToMany('App\NivelUsuario');
    }

}
