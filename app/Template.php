<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'template';

    protected $fillable = [
        'encaminhar',
        'dias_enviar',
        'dias_semana',
        'hora_inicial',
        'hora_final',
        'assunto',
        'mensagem',
    ];

    protected $casts = [
        'encaminhar' => 'boolean',
    ];

    public function sequencias() {
        return $this->belongsToMany('App\Sequencia');
    }

}
