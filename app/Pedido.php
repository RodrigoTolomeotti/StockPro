<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'pedido';

    protected $fillable = [
        'usuario_id',
        'cliente_id',
        'valor_total',
        'data_liberacao',
        'data_entrega',
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

    public function itensPedido() {
        return $this->hasMany('App\ItemPedido');
    }

}
