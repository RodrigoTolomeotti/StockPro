<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoProduto extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'tipo_produto';

    protected $fillable = [
        'nome',
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }

}
