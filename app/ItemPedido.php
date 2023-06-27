<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'item_pedido';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'usuario_id',
        'preco_unitario',
        'desconto',
        'quantidade',
    ];

    // public function usuario() {
    //     return $this->belongsTo('App\Usuario');
    // }

    public function pedido() {
        return $this->hasOne('App\Pedido');
    }

    public function produto() {
        return $this->hasOne('App\Produto');
    }

}
