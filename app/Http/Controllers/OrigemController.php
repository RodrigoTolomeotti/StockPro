<?php

namespace App\Http\Controllers;

use App\Origem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrigemController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {
        try{

            $query = Origem::query();

            if ($request->has('ativa') && $request->input('ativa') != '') {
                $query->whereNull('data_inativacao');
            }

            return response()->json([
                'data' => $query->get()
            ]);



        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);
        }
    }

}
