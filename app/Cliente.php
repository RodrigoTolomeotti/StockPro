<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'cliente';

    protected $fillable = [
      'nome',
      'telefone',
      'email',
      'endereco',
      'cpf_cnpj'
    ];

    public function usuario() {
      return $this->hasOne('App\Usuario');
    }
}
