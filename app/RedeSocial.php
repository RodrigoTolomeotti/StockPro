<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RedeSocial extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'rede_social';

    protected $fillable = [
        'nome'
    ];

}
