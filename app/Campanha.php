<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'campanha';

    protected $fillable = [
        'nome',
        'data_inicio',
        'cargo_id',
        'departamento_id',
        'profissao_id',
        'origem_id'
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }

    public function cargos() {
        return $this->belongsToMany('App\Cargo');
    }

    public function departamentos() {
        return $this->belongsToMany('App\Departamento');
    }

    public function profissoes() {
        return $this->belongsToMany('App\Profissao');
    }

    public function grupos_contato() {
        return $this->belongsToMany('App\GrupoContato');
    }

    public function sequencias() {
        return $this->hasMany('App\Sequencia');
    }

    public function sequencia() {
        return $this->belongsTo('App\Sequencia');
    }

}
