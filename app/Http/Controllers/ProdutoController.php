<?php

namespace App\Http\Controllers;

use App\Produto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $query = $this->user->produtos();

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('nome', 'like', '%' . $request->input('nome') . '%');
            }

            $total_rows = $query->count();

            if ($request->has('offset')) {
                $offset = $request->input('offset');
                $query->offset($offset);
            }

            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $query->limit($limit);
            }

            if ($request->has('sort_by')) {
                $sort_by = $request->input('sort_by');
                $order_by = $request->input('order_by', 'asc');
                $query->orderBy($sort_by, $order_by);
            }

            return response()->json([
                'data' => $query->get(),
                'totalRows' => $total_rows
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }
    private function getValidation() {
        return [
            'nome'              => ['required', 'string', 'max:128'],
            'custo'             => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'preco_unitario'    => ['nullable', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
            'tipo_produto_id'   => ['required', 'exists:tipo_produto,id'],
            'descricao'         => ['required', 'string'],
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $produtos = $this->user->produtos()->create($request->input());
            return ['data' => $produtos];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $produtos = Produto::find($id);

            if (!$produtos) return [
                'errors' => ['produto não encontrado']
            ];
            $produtos->update($request->input());

            return ['data' => $produtos];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $produtos = $this->user->produtos()->find($id);

        try{
            $produtos->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }

    public function uploadImagem(Request $request) {

        try {
            $produto = Produto::find($request->id);
            if (!isset($_FILES['imagem'])) {
                throw new Exception('Imagem não encontrada');
            }

            $path = "../public/users/products/";
            $file_name = Str::random(25) . ".jpg";

            $file = $_FILES['imagem']['tmp_name'];
            $save = $path . $file_name;

            list($width, $height) = getimagesize($file);
            $info = getimagesize($file);

            switch ($info['mime']) {
                case 'image/jpg':
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file);
                break;
                case 'image/gif':
                    $image = imagecreatefromgif($file);
                break;
                case 'image/png':
                case 'image/x-png':
                    $image = imagecreatefrompng($file);
                break;
                default:
                    throw new Exception('Tipo do arquivo não suportado');
                break;
            }

            $newWidth = 1920;
            $newHeight = 1200;

            $xPosition = 0;
            $yPosition = 0;
            
            if($width_orig > $height_orig){
                $height = ($width/$width_orig)*$height_orig;
                // Se altura é maior que largura, dividimos a altura determinada pela original e multiplicamos a largura pelo resultado, para manter a proporção da imagem
            } elseif($width_orig < $height_orig) {
                $width = ($height/$height_orig)*$width_orig;
                } // -> fim if
            // if ($width > $height) {
            //     $xPosition = $width/2 - $height/2;
            //     $width = $height;
            // }

            // if ($height > $width) {
            //     $yPosition = $height/2 - $width/2;
            //     $height = $width;
            // }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresampled($newImage, $image, 0, 0, $xPosition, $yPosition, $newWidth, $newHeight, $width, $height);

            imagejpeg($newImage, $save, 100);

            if ($produto->imagem) unlink($path . $produto->imagem);
            $produto->imagem = $file_name;
            $produto->save();

            return response()->json([
                'imagem' => $file_name
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }
}
