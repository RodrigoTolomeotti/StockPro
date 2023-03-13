<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'contato';

    protected $fillable = [
        'nome',
        'empresa',
        'cpf_cnpj',
        'email',
        'telefone',
        'cargo_id',
        'departamento_id',
        'profissao_id',
        'grupo_contato_id',
        'facebook_link',
        'linkedin_link',
        'instagram_link',
        'twitter_link',
    ];

    public function usuario() {
        return $this->hasOne('App\Usuario');
    }

    public function cargo() {
        return $this->belongsTo('App\Cargo');
    }

    public function departamento() {
        return $this->belongsTo('App\Departamento');
    }

    public function profissao() {
        return $this->belongsTo('App\Profissao');
    }

    public function grupos_contato() {
        return $this->belongsTo('App\GrupoContato');
    }

    public static function findByContatoEmail($usuario, $email, $columns = ['*']) {

        $query = self::query();

        $query->where('usuario_id', '=', $usuario)
              ->where('email', '=', $email);

        return $query->first($columns);

    }



}
