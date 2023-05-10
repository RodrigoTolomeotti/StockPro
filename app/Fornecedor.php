<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'fornecedor';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'cpf_cnpj',
        'produto_id'
    ];

    public function usuario() {
        return $this->belongsTo('App\Usuario');
    }

    public function produto() {
        return $this->belongsTo('App\Produto');
    }

    public static function findByFornecedorNome($usuario, $nome, $columns = ['*']) {

        $query = self::query();

        $query->where('usuario_id', '=', $usuario)
              ->whereRaw('upper(nome) = '. '\''.$nome.'\'');
        return $query->first($columns);

    }

}
