<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sequencia extends Model
{
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';

    public $table = 'sequencia';

    protected $fillable = [];

    public function campanha() {
        return $this->belongsTo('App\Campanha');
    }

    public function templates() {
        return $this->belongsToMany('App\Template');
    }

}
