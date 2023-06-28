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
        'preco_unitario',
        'tipo_produto_id',
        'fornecedor_id',
        'descricao',
        'imagem',
        'quantidade'
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

}
