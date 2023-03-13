<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'notificacao';

    protected $fillable = [
        'titulo',
        'mensagem',
        'usuario_id'
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

}
