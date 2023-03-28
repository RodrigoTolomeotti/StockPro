<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Usuario extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'usuario';

    protected $fillable = [
        'nome', 'email', 'senha', 'token', 'imagem',
        'conta_usuario', 'conta_email', 'conta_senha',
        'smtp_host', 'smtp_port', 'smtp_security',
        'imap_host', 'imap_port', 'imap_security', 'assinatura',
        'nivel_usuario_id', 'data_inativacao'
    ];

    protected $hidden = [
        'senha', 'conta_senha', 'token'
    ];

    public function clientes() {
        return $this->hasMany('App\Cliente');
    }

    public function erros() {
        return $this->hasMany('App\Erro');
    }

    public function notificacoes() {
        return $this->hasMany('App\Notificacao');
    }

    public function tokens() {
        return $this->hasMany('App\Token');
    }

    public function nivelUsuario() {
        return $this->belongsTo('App\NivelUsuario');
    }

}
