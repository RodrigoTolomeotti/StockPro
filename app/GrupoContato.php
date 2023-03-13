<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupoContato extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'grupo_contato';

    protected $fillable = [
        'nome'
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

    public static function findByGrupoContatoNome($usuario, $nome, $columns = ['*']) {

        $query = self::query();

        $query->where('usuario_id', '=', $usuario)
              ->whereRaw('upper(nome) = '. '\''.$nome.'\'');
        return $query->first($columns);

    }

}
