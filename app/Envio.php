<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'envio';

    protected $fillable = ['data_bounce'];

    public function Contatos() {
        return $this->belongsToMany('App\Contatos');
    }

    public function sequencias() {
        return $this->belongsToMany('App\Sequencia');
    }

    public function templates() {
        return $this->belongsToMany('App\Template');
    }

}
