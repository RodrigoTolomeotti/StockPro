<?php

namespace App\Http\Controllers;

use App\Pedido;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {
            $query = $this->user->pedidos()
            ->select('pedido.id',
                'pedido.usuario_id',
                'cliente.nome',
                'pedido.cliente_id',
                'pedido.valor_total',
                'pedido.data_entrega',
                'pedido.data_criacao'
            )
            ->join('cliente', 'pedido.cliente_id', '=', 'cliente.id');

            if ($request->has('id') && $request->input('id') != '') {
                $query->where('pedido.id', '=', $request->input('id'));
            }

            if ($request->has('cliente_id') && $request->input('cliente_id') != '') {
                $query->where('pedido.cliente_id', '=', $request->input('cliente_id'));
            }

            if ($request->has('valor_total') && $request->input('valor_total') != '') {
                $query->where('pedido.valor_total', 'like', '%' . $request->input('valor_total') . '%');
            }            
            
            if ($request->has('data_entrega') && $request->input('data_entrega') != '') {
                $query->where('pedido.data_entrega', '=', $request->input('nome'));
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
            'valor_total'      => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cliente_id'       => ['required', 'exists:cliente,id'],
            'data_liberacao'   => ['nullable', 'date'],
            'data_entrega'     => ['nullable', 'date'],
        ];
    }

    public function create(Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $pedido = $this->user->pedidos()->create($request->input());
            return ['data' => $pedido];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $this->validate($request, $this->getValidation());

            $pedidos = Pedido::find($id);

            if (!$pedidos) return [
                'errors' => ['Pedido não encontrado']
            ];
            $pedidos->update($request->input());

            return ['data' => $pedidos];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $pedidos = $this->user->pedidos()->find($id);

        try{
            $pedidos->delete();
        }catch(\Exception $e){
            return ['data' => false];
        }

        return ['data' => true];
    }
}
