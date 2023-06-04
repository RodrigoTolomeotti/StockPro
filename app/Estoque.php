<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'estoque';

    protected $fillable = [
        'quantidade',
        'produto_id',
        'fornecedor_id'
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }    
    
    public function produto() {
        return $this->belongsTo('App\Produto');
    }

}
