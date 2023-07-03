<?php

namespace App\Http\Controllers;

use App\itemPedido;
use App\Pedido;
use App\Produto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class itemPedidoController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function getAll(Request $request)
    {

        try {

            $itemsPedido = Pedido::find($request->input('pedido_id'))->itensPedido();

            if ($request->has('produto_id') && $request->input('produto_id') != '') {
                $itemsPedido->where('produto_id', '=',$request->input('produto_id'));
            }

            if ($request->has('preco_unitario') && $request->input('preco_unitario') != '') {
                $itemsPedido->where('preco_unitario', '=',$request->input('preco_unitario'));
            }

            if ($request->has('quantidade') && $request->input('quantidade') != '') {
                $itemsPedido->where('quantidade', '=',$request->input('quantidade'));
            }

            $total_rows = $itemsPedido->count();
            
            if ($request->has('offset')) {
                $offset = $request->input('offset');
                $itemsPedido->offset($offset);
            }
            
            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $itemsPedido->limit($limit);
            }
            
            if ($request->has('sort_by')) {
                $sort_by = $request->input('sort_by');
                $order_by = $request->input('order_by', 'asc');
                $itemsPedido->orderBy($sort_by, $order_by);
            }
            
            $itemsPedido = $itemsPedido->get();
            
            return response()->json([
                'data' => $itemsPedido,
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
            // 'pedido_id'        => ['required', 'exists:pedido,id'],
            'produto_id'       => ['required', 'exists:produto,id'],
            'preco_unitario'   => ['required', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
            'desconto'         => ['nullable', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
            'quantidade'       => ['nullable', 'numeric', 'min:1', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function create(Request $request) {
        try {

            $object = $request->input();
            $produto = Produto::find($request->input('produto_id'));

            if(!$object['quantidade']) {
                throw ValidationException::withMessages(['incorrect' => 'Quantidade obrigatória 😢']);
            }
            
            $this->validate($request, $this->getValidation());
            
            if(!$request->input('pedido_id'))
                throw ValidationException::withMessages(['save' => 'Salve o cabeçalho do pedido!']);

            if(!$this->checkStockProduct($produto, $object['quantidade']))
                throw ValidationException::withMessages(['incorrect' => 'Produto sem estoque 😢']);

            $itemPedido = $this->user->itensPedido()->create($object);

            $itemPedido->update($object);

            $valorTotal = $this->calculeValuePedido($itemPedido->pedido_id);

            if(!$valorTotal)
                throw ValidationException::withMessages(['incorrect' => 'Erro ao calcular o pedido! 😢']);
            
            $itemPedido->valor_total = $valorTotal;

            return ['data' => $itemPedido];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];

        }
    }

    public function update($id, Request $request) {
        try {
            $object = $request->input();
            $produto = Produto::find($request->input('produto_id'));
            
            if(!$object['quantidade']) {
                throw ValidationException::withMessages(['incorrect' => 'Quantidade obrigatória 😢']);
            }
            
            $this->validate($request, $this->getValidation());
            
            if(!$request->input('pedido_id'))
                throw ValidationException::withMessages(['save' => 'Salve o cabeçalho do pedido!']);

            if(!$this->checkStockProduct($produto, $object['quantidade']))
                throw ValidationException::withMessages(['incorrect' => 'Produto sem estoque 😢']);

            $itemPedido = itemPedido::find($id);

            if (!$itemPedido) return [
                'errors' => ['Pedido não encontrado']
            ];

            $itemPedido->update($object);

            $valorTotal = $this->calculeValuePedido($itemPedido->pedido_id);

            if(!$valorTotal)
                throw ValidationException::withMessages(['incorrect' => 'Erro ao calcular o pedido! 😢']);
            
            $itemPedido->valor_total = $valorTotal;

            return ['data' => $itemPedido];

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }
    }

    public function delete(Request $request, $id) {

        $itemPedido = $this->user->itensPedido()->find($id);
        $pedidoId = $itemPedido->pedido_id;

        try{

            $itemPedido->delete();
            $valorTotal = $this->calculeValuePedido($pedidoId);
            if($valorTotal === false)
                throw ValidationException::withMessages(['incorrect' => 'Erro ao calcular o pedido! 😢']);

        } catch (ValidationException | Exception $e) {

            return ['errors' => $e->errors()];
        }

        return ['data' => ['valor_total' => $valorTotal]];
    }

    public function checkStockProduct($produto, $quantidadePedida) {
        
        $quantidadeAtual = $produto->quantidade;

        $produto->quantidade = $produto->quantidade - $quantidadePedida;
        if($produto->quantidade <= 0)
            return false;
        
        $produto->save();
        return true;


    }

    public function calculeValuePedido($id) {
        $pedido = Pedido::find($id);
        $itensPedido = $pedido->itensPedido;
        
        $valorTotal = 0;
        
        foreach ($itensPedido as $itemPedido) {
            $precoUnitario = $itemPedido->preco_unitario;
            $quantidade = $itemPedido->quantidade;
            $valorItem = $precoUnitario * $quantidade;
            
            $valorTotal += $valorItem;
        }
        
        $pedido->valor_total = $valorTotal;
        $pedido->save();

        return $valorTotal;

    }
}
