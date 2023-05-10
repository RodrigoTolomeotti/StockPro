<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'tipo_produto';

    protected $fillable = [
        'quantidade_disponível',
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }    
    
    public function produto() {
        return $this->belongsTo('App\Produto');
    }

}
