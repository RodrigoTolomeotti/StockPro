<?php

namespace App\Http\Controllers;

use App\NivelUsuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class NivelUsuarioController extends NivelUsuario
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll()
    {

        return ['data' => NivelUsuario::orderBy('nome')->get()];

    }

}
