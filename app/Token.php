<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'token';

    protected $fillable = [
        'token',
        'data_expiracao',
        'ip',
        'user_agent'
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }

}
