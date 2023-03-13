<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Origem extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'origem';

    protected $fillable = [
        'nome',
        'origem',
        'ordem'
    ];

}
