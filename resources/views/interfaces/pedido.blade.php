@extends('layouts.main')

@section('title', 'Pedido')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Pedido" title="Pedido">
        <template slot="icons">
            <i @click="novoPedido" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>
        <c-list :items="pedidos" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column align-items-start w-50">
                    <div>
                        <span style="font-size: .9em; color: #222;">Pedido <b>@{{item.data.id}}</b></span>
                    </div>
                    <div>
                        <span style="font-size: .9em; color: #222;">Cliente <b>@{{item.data.nome}}</b></span>
                    </div>
                    </div>
                    <div class="d-flex flex-column align-items-start w-25">
                    <div>
                        <span style="font-size: 1em; color: #222;">Valor Total R$ <b>@{{item.data.valor_total}}</b></span>
                    </div>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                    <div>
                        <span v-if="item.data.data_criacao" style="font-size: .8em; color: #6f6f6f;">Dt. Criação: @{{item.data.data_criacao | datetimeCriacao}}</span>
                    </div>
                    <div>
                        <span v-if="item.data.data_entrega" style="font-size: .8em; color: #6f6f6f;">Dt. Entrega: @{{item.data.data_entrega | datetime}}</span>
                    </div>
                    </div>
                    <div class="d-flex align-items-center ml-2">
                    <div class="list-icons">
                        <i @click="editarPedido(item.data)" class="fas fa-pen"></i>
                        <i @click="idPedidoExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
                    </div>
                    </div>
                </div>
            </template>
        </c-list>

        <span class="card-divisor mb-3"></span>

        <div class="d-flex justify-content-between">
            <span style="font-size: .8em;font-weight: bold;">
                @{{(page - 1) * limit + 1}} - @{{page * limit > totalRows ? totalRows : page * limit}} / @{{totalRows}}
            </span>
            <b-pagination
                v-model="page"
                :total-rows="totalRows"
                :per-page="limit"
                aria-controls="my-table"
                pills
                size="sm"
                class="mb-0"
            ></b-pagination>
        </div>
    </c-card>
    
    <c-modal title="Pedidos" v-model="modalPedido" size="lg">
        <div class="tabs mb-2">
            <div v-for="tab in tabs"
                    class="tab"
                    @click="activeTab = tab.value"
                    :class="{'active-tab': tab.active}">
                @{{tab.text}}
            </div>
        </div>
        <transition name="tab-content">
            <div v-if="tabs[0].active" class="p-3">
                <b-row>
                    <b-col cols="12">
                        <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                            <b-form-select
                                v-if="field.attribute === 'cliente_id'"
                                id="field.attribute"
                                v-model="pedido[field.attribute]"
                                :options="optionsClientes">
                                <template v-slot:first>
                                    <option :value="null">Selecione um</option>
                                </template>
                            </b-form-select>
                            <b-form-input 
                                v-else-if="field.type === 'date'"
                                type="date"
                                id="field.attribute"
                                v-model="pedido[field.attribute]">
                            </b-form-input>

                            <b-form-select
                                v-else-if="field.attribute === 'tipo_estoque'"
                                id="id"
                                v-model="pedido['tipo_estoque']"
                                :options="optionsTipoEstoque">
                                <template v-slot:first>
                                    <option :value="null">Selecione um</option>
                                </template>
                            </b-form-select>
                            <b-form-input
                                v-else
                                id="field.attribute"
                                v-model="pedido[field.attribute]"
                                type="text"
                            ></b-form-input>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-btn @click="salvarPedido" class="float-right" variant="primary">Salvar Cabeçalho</b-btn>
            </div>

            <div v-if="tabs[1].active" class="p-3">

                <b-row>
                    <b-col cols="12">
                        <b-form-group label-size="sm" v-for="field, i in commonFieldsItems" :key="i" :label="field.name" :label-for="field.attribute">
                            <b-form-select
                                v-if="field.attribute === 'produto_id'"
                                id="field.attribute"
                                v-model="pedido[field.attribute]"
                                :options="optionsProdutos">
                                <template v-slot:first>
                                    <option :value="null">Selecione um</option>
                                </template>
                            </b-form-select>
                            <b-form-input
                                v-else-if="field.attribute === 'preco_unitario'"
                                    id="field.attribute"
                                    v-model="precoUnitario"
                                    type="text"
                                    :readonly="true"
                            ></b-form-input>
                            <!-- <b-form-input
                                v-else
                                id="field.attribute"
                                v-model="pedido[field.attribute]"
                                type="text"
                            ></b-form-input> -->
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-btn @click="salvarPedido" class="float-right" variant="primary">Salvar Cabeçalho</b-btn>

            </div>

        </transition>
    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarPedido">Filtrar</button>
        </template>

        <b-row>
            <b-col>

                <b-form-group label-size="sm" label="Nome" label-for="filtro-nome">
                    <b-form-input
                        id="filtro-nome"
                        v-model="temporaryFilters.nome"
                        type="text"
                    ></b-form-input>
                </b-form-group>

            </b-col>
        </b-row>

    </c-modal>

    <c-modal v-model="excluirModal" title="Excluir Pedido" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirTipoProduto" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o pedido?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                activeTab: 1,
                dontActiveTab: 1,
                produtos: [],
                produto: {},
                pedidos: {},
                pedido: [],
                clientes: [],
                cliente: {},
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalPedido: false,
                modalFiltro: false,
                tipoProduto: {},
                commonFields: [
                    {name: 'Cliente', attribute: 'cliente_id', type: 'select'},
                    {name: 'Valor Total', attribute: 'valor_total', type: 'text'},
                    // {name: 'Data Liberação', attribute: 'data_liberacao', type: 'date'},
                    {name: 'Data Entrega', attribute: 'data_entrega', type: 'date'},
                ],
                commonFieldsItems: [
                    {name: 'Produto', attribute: 'produto_id', type: 'select'},
                    {name: 'Preço Unitário', attribute: 'preco_unitario', type: 'text'},
                    {name: 'Desconto', attribute: 'desconto', type: 'number'},
                    {name: 'Quantidade', attribute: 'desconto', type: 'number'}
                ],
                excluirModal: false,
                idPedidoExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                },
                tipoPedido: [
                    {name: 'Entrada', value: 1},
                    {name: 'Saída', value: 2},
                ],
                mesesAbreviados: [
                    "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                    "Jul", "Ago", "Set", "Out", "Nov", "Dez"
                ]
            }
        },
        methods: {
            loadPedido() {

                axios.get('api/pedido', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'pedido.id',
                        ...this.filters
                    }
                }).then(res => {

                    this.pedidos = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoPedido() {
                this.pedido = {
                    cliente_id: null,
                    data_entrega: null,
                    valor_total: null
                }
                this.modalPedido = true
            },
            editarPedido(pedido) {
                this.pedido = JSON.parse(JSON.stringify(pedido))
                this.modalPedido = true
            },
            excluirPedido() {

                axios.delete('api/pedido/' + this.idPedidoExcluir).then(res => {
                    this.excluirModal = false
                    this.loadPedido()

                    if(res.data.data){
                        store.alerts.push({text: 'Pedido excluído com sucesso', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir Pedido', variant:'danger'})
                    }
                })

            },
            salvarPedido() {

                if (this.pedido.id) {
                    axios.put('api/pedido/' + this.pedido.id, {
                        ...this.pedido
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.loadPedido()
                        store.alerts.push({text: 'Pedido alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/pedido', {
                        ...this.pedido
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.loadPedido()
                        store.alerts.push({text: 'Pedidos incluído com sucesso', variant:'success'})
                    })
                }
            },
            atualizarPrecoUnitario() {
                console.log(1, produtoSelecionado.preco_unitario)
                const produtoIdSelecionado = this.pedido.produto_id;
                const produtoSelecionado = this.produtos.find(produto => produto.id === produtoIdSelecionado);
                if (produtoSelecionado) {
                    this.pedido.preco_unitario = produtoSelecionado.preco;
                } else {
                    this.pedido.preco_unitario = null;
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarPedido() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadPedido()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadPedido()
                })
            },
            loadProdutos() {
                axios.get('api/produto').then(res => {
                    this.produtos = res.data.data;
                })
            },
            loadclientes() {
                axios.get('api/cliente').then(res => {
                    this.clientes = res.data.data;
                })
            }
        },
        watch: {
            page() {
                this.loadPedido()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
            optionsProdutos() {
                return this.produtos.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsClientes() {
                return this.clientes.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsTipoEstoque() {
                return this.tipoEstoque.map(o => ({
                    value: o.value,
                    text: o.name
                }))
            },
            isPedidoChanged() {
                if(this.pedido.cliente_id != null 
                || this.pedido.valor_total != null
                || this.pedido.data_entrega != null) return true
            },
            isItemPedidoChanged() {
                if(this.pedido.produto_id != null 
                || this.pedido.preco_unitario != null
                || this.pedido.desconto != null
                || this.pedido.quantidade != null
                ) return true
            },
            tabs() {
                return [
                    {value: 1, text: 'Cabeçalho' + (this.isPedidoChanged ? ' •' : ''), active: this.activeTab == 1},
                    {value: 2, text: 'Item' + (this.isItemPedidoChanged ? ' •' : ''), active: this.activeTab == 2},
                ]
            },
            precoUnitario() {
                const produtoSelecionado = this.produtos.find(produto => produto.id === this.pedido.produto_id);
                console.log(3, produtoSelecionado)
                if (produtoSelecionado) {
                    return produtoSelecionado.preco_unitario;
                }
                return null;
            }
        },
        filters: {
            datetime(date) {
                if(date) {
                    let arr = date.split(' ');
                    let data = arr[0].split('-');
                    let dia = data[2];
                    let mesAbreviado = mesesAbreviados[parseInt(data[1]) - 1];
                    let ano = data[0];
                    
                    return `${dia}-${mesAbreviado}-${ano}`;
                }
            },
            datetimeCriacao(data) {
                const partes = data.split("-"); // Divide a string da data em partes usando o separador "-"
                const ano = partes[0];
                const mes = partes[1];
                const diaHora = partes[2].split("T"); // Divide a parte do dia e hora usando o separador "T"
                const dia = diaHora[0];
                
                // Mapeia os meses em um array para obter o formato de três letras
                this.mesesAbreviados = [
                    "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                    "Jul", "Ago", "Set", "Out", "Nov", "Dez"
                ];
                console.log(this.mesesAbreviados)
                const mesAbreviado = this.mesesAbreviados[parseInt(mes) - 1];
                
                return `${dia}-${mesAbreviado}-${ano}`;
            }
        },
        created() {
            this.loadPedido(),
            this.loadProdutos(),
            this.loadclientes()
            
        },
        template: `@yield('interface')`
    })

</script>
@endpush
@push('styles')

<style>
.tabs {
    display: flex;
}

.tab {
    padding: .7em 1em;
    color: #ccc;
    cursor: pointer;
    transition: .2s;
    border-bottom: 3px solid #fff;
}

.tab.active-tab {
    color: #65a2ff;
    border-bottom: 3px solid #65a2ff;
}

.tab-content-enter-active {
    transform: translateX(30px);
    opacity: 0;
    transition: .5s;
}

.tab-content-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.tab-content-leave-active {
    display: none;
}
</style>
