<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'produto';

    protected $fillable = [
        'nome',
        'custo',
        'preco_unitario',
        'tipo_produto_id',
        'descricao',
        'imagem'
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

    public function estoque() {
        return $this->hasMany('App\Estoque');
    }

}
