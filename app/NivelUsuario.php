<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NivelUsuario extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'nivel_usuario';

    protected $fillable = [
        'nome'
    ];

    public function telas() {
        return $this->belongsToMany('App\Tela');
    }

    public function usuarios() {
        return $this->hasMany('App\Usuario');
    }

}
