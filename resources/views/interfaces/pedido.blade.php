@extends('layouts.main')

@section('title', 'Pedido')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Pedido" title="Pedido">
        <template slot="icons">
            <i @click="novoPedido(); dontActiveTab = 2" class="fas fa-plus fa-lg"></i>
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
                        <i @click="editarPedido(item.data); dontActiveTab = 0" class="fas fa-pen"></i>
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
    
    <c-modal title="Pedidos" v-model="modalPedido" size="xl" style="overflow: none !important">
        <div class="tabs mb-2">
            <div 
                v-for="tab in tabs"
                class="tab"
                @click="activeTab = tab.value"
                :class="{'active-tab': tab.active}">
                <span v-if="dontActiveTab !== tab.value">@{{tab.text}}</span>
                
            </div>
        </div>
        <transition name="tab-content">
            <div v-if="tabs[0].active" class="p-3">
                <b-row>
                    <b-col cols="12">
                        <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                            <b-form-select
                                cols="2"
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
                            <b-form-input 
                                v-else-if="field.type === 'readonly'"
                                type="text"
                                id="field.attribute"
                                v-model="pedido[field.attribute]"
                                readonly>
                            </b-form-input>
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
                <c-card title="Item Pedido">
                    <template slot="icons">
                        <i @click="novoItemPedido" class="fas fa-plus fa-lg"></i>
                        <i @click="abrirFiltrosItemPedido" class="fas fa-filter fa-lg">
                            <span v-if="totalFiltersItemPedido" class="card-icons-alert">@{{totalFiltersItemPedido}}</span>
                        </i>
                    </template>
                    <c-list :items="itemsPedido" :loading="loading" class="mb-0">
                        <template v-slot:items="item">
                            <div class="d-flex justify-content-between w-100">
                                <div class="d-flex flex-column align-items-start w-50">
                                <div>
                                    <span style="font-size: .9em; color: #222;">Item <b>@{{item.data.id}}</b></span>
                                </div>
                                <div>
                                    <span style="font-size: .9em; color: #222;">Produto <b>@{{item.data.produto_id}}</b></span>
                                </div>
                                </div>
                                <div class="d-flex flex-column align-items-start w-25">
                                <div>
                                    <span style="font-size: 1em; color: #222;">Preco Unitário R$ <b>@{{item.data.preco_unitario}}</b></span>
                                </div>
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                <div>
                                    <span v-if="item.data.data_criacao" style="font-size: .8em; color: #6f6f6f;">Dt. Criação: @{{item.data.data_criacao | datetimeCriacao}}</span>
                                </div>
                                <!-- <div>
                                    <span v-if="item.data.data_entrega" style="font-size: .8em; color: #6f6f6f;">Dt. Entrega: @{{item.data.data_entrega | datetime}}</span>
                                </div> -->
                                </div>
                                <div class="d-flex align-items-center ml-2">
                                    <div class="list-icons">
                                        <i @click="editarItemPedido(item.data); editItemPedido = true" class="fas fa-pen"></i>
                                        <i @click="idItemPedidoExcluir = item.data.id; excluirItemPedidoModal = true" class="fas fa-trash-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </c-list>
                    <span class="card-divisor mb-3"></span>

                    <div class="d-flex justify-content-between">
                        <span style="font-size: .8em;font-weight: bold;">
                            @{{(pageItemPedido - 1) * limitItemPedido + 1}} - @{{pageItemPedido * limitItemPedido > totalRowsItemPedido ? totalRowsItemPedido : pageItemPedido * limitItemPedido}} / @{{totalRowsItemPedido}}
                        </span>
                        <b-pagination
                            v-model="pageItemPedido"
                            :total-rows="totalRowsItemPedido"
                            :per-page="limitItemPedido"
                            aria-controls="my-table"
                            pills
                            size="sm"
                            class="mb-0"
                        ></b-pagination>
                    </div>
                </c-card>


            </div>

        </transition>
    </c-modal>
    <c-modal title="Item Pedido" v-model="modalItemPedido" size="lg">
        <template v-slot:buttons>
            <button @click="salvarItemPedido">Salvar</button>
        </template>
        <b-row>
            <b-col cols="12">
                <b-form-group label-size="sm" v-for="field, i in commonFieldsItems" :key="i" :label="field.name" :label-for="field.attribute">
                    <b-form-select
                        v-if="field.attribute === 'produto_id'"
                        id="field.attribute"
                        v-model="itemPedido[field.attribute]"
                        @change="precoManual = false"
                        :options="optionsProdutos"
                        :disabled="editItemPedido">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-input
                        v-else-if="field.attribute === 'preco_unitario'"
                        id="field.attribute"
                        v-model="itemPedido.preco_unitario"
                        type="text"
                        @change="precoManual = true"
                    ></b-form-input>
                    <b-form-input
                        v-else
                        id="field.attribute"
                        v-model="itemPedido[field.attribute]"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>
    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarPedido">Filtrar</button>
        </template>

        <b-row>
            <b-col cols="12">
                <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                    <b-form-select
                        v-if="field.attribute === 'cliente_id'"
                        id="field.attribute"
                        v-model="temporaryFilters[field.attribute]"
                        :options="optionsClientes">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-input 
                        v-else-if="field.type === 'date'"
                        type="date"
                        id="field.attribute"
                        v-model="temporaryFilters[field.attribute]">
                    </b-form-input>
                    <b-form-input
                        v-else
                        id="field.attribute"
                        v-model="temporaryFilters[field.attribute]"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <!-- <b-form-group label-size="sm" label="Nome" label-for="filtro-nome">
                    <b-form-input
                        id="filtro-nome"
                        v-model="temporaryFilters.nome"
                        type="text"
                    ></b-form-input>
                </b-form-group> -->
            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltroItemPedido" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltrosItemPedido">Limpar</button>
            <button @click="filtrarItemPedido">Filtrar</button>
        </template>

        <b-row>
            <b-col cols="12">
                <b-form-group label-size="sm" v-for="field, i in commonFieldsItems" :key="i" :label="field.name" :label-for="field.attribute">
                    <b-form-select
                        v-if="field.attribute === 'produto_id'"
                        id="field.attribute"
                        v-model="temporaryFiltersItemPedido[field.attribute]"
                        :options="optionsProdutos">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-input
                        v-else-if="field.attribute === 'preco_unitario'"
                        id="field.attribute"
                        v-model="temporaryFiltersItemPedido[field.attribute]"
                        type="text"
                        @change="precoManual = true"
                    ></b-form-input>
                    <b-form-input
                        v-else
                        id="field.attribute"
                        v-model="temporaryFiltersItemPedido[field.attribute]"
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
    <c-modal v-model="excluirItemPedidoModal" title="Excluir Pedido" size="md">

        <template v-slot:buttons>
            <button @click="excluirItemPedidoModal = false" size="sm">Não</button>
            <button @click="excluirItemPedido" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o item do pedido?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                activeTab: 1,
                dontActiveTab: 2,
                produtos: [],
                produto: {},
                pedidos: {},
                pedido: [],
                itemPedido: [],
                itemsPedido: [],
                clientes: [],
                cliente: {},
                limit: 15,
                page: 1,
                totalRows: 0,
                limitItemPedido: 15,
                pageItemPedido: 1,
                totalRowsItemPedido: 0,
                loading: false,
                modalPedido: false,
                modalItemPedido: false,
                modalFiltro: false,
                modalFiltroItemPedido: false,
                tipoProduto: {},
                commonFields: [
                    {name: 'Id', attribute: 'id', type: 'readonly'},
                    {name: 'Cliente', attribute: 'cliente_id', type: 'select'},
                    {name: 'Valor Total', attribute: 'valor_total', type: 'readonly'},
                    // {name: 'Data Liberação', attribute: 'data_liberacao', type: 'date'},
                    {name: 'Data Entrega', attribute: 'data_entrega', type: 'date'},
                ],
                commonFieldsItems: [
                    {name: 'Produto', attribute: 'produto_id', type: 'select'},
                    {name: 'Preço Unitário', attribute: 'preco_unitario', type: 'text'},
                    // {name: 'Desconto', attribute: 'desconto', type: 'number'},
                    {name: 'Quantidade', attribute: 'quantidade', type: 'number'}
                ],
                excluirModal: false,
                idPedidoExcluir: false,
                idItemPedidoExcluir: false,
                temporaryFilters: {},
                temporaryFiltersItemPedido: {},
                filters: {
                    nome: null
                },
                filtersItemPedido: {
                    produto_id: null,
                    preco_unitario: null,
                    quantidade: null
                },
                tipoPedido: [
                    {name: 'Entrada', value: 1},
                    {name: 'Saída', value: 2},
                ],
                mesesAbreviados: [
                    "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                    "Jul", "Ago", "Set", "Out", "Nov", "Dez"
                ],
                excluirItemPedidoModal: false,
                precoManual: false,
                editItemPedido: false
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
                    id: null,
                    cliente_id: null,
                    data_entrega: null,
                    valor_total: null
                }
                this.itemsPedido = {}
                this.activeTab = 1;
                this.modalPedido = true
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
            editarPedido(pedido) {
                this.temporaryFiltersItemPedido, this.filtersItemPedido = {}
                this.activeTab = 1;
                this.pedido = JSON.parse(JSON.stringify(pedido))
                this.modalPedido = true
                this.loadItemPedido();
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
                        this.filters.id = res.data.data.id
                        this.loadPedido()
                        store.alerts.push({text: 'Pedido incluído com sucesso', variant:'success'})
                    })
                }
                this.modalPedido = false;
            },
            loadItemPedido() {
                axios.get('api/item-pedido', {
                    params: {
                        pedido_id: this.pedido.id,
                        limit: this.limitItemPedido,
                        offset: this.pageItemPedido * this.limitItemPedido - this.limitItemPedido,
                        sort_by: 'item_pedido.id',
                        ...this.filtersItemPedido
                    }
                }).then(res => {

                    this.itemsPedido = res.data.data

                    this.totalRowsItemPedido = res.data.totalRows

                    this.loading = false

                })
            },
            novoItemPedido() {
                this.itemPedido = {
                    pedido_id: this.pedido.id,
                    produto_id: null,
                    preco_unitario: null,
                    desconto: null,
                    quantidade: null
                }
                this.editItemPedido = false;
                this.modalItemPedido = true;
            },
            editarItemPedido(itemPedido) {
                this.itemPedido = JSON.parse(JSON.stringify(itemPedido))
                this.modalItemPedido = true
            },
            excluirItemPedido() {
                axios.delete('api/item-pedido/' + this.idItemPedidoExcluir).then(res => {

                    if (res.data.errors) {
                        Object.keys(res.data.errors).forEach(k => {
                            res.data.errors[k].forEach(e => {
                                store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                            })
                        })
                        return;
                    }
                    this.excluirItemPedidoModal = false
                    store.alerts.push({text: 'Item do pedido excluído com sucesso', variant:'success'})
                    this.pedido.valor_total = res.data.data.valor_total;
                    this.loadItemPedido()
                })
            },
            salvarItemPedido() {
                if (this.itemPedido.id) {
                    axios.put('api/item-pedido/' + this.itemPedido.id, {
                        ...this.itemPedido
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalItemPedido = false;
                        this.loadItemPedido()
                        this.pedido.valor_total = res.data.data.valor_total;
                        store.alerts.push({text: 'Item do pedido alterado com sucesso', variant:'success'})
                    })
                } else {
                    axios.post('api/item-pedido', {
                        ...this.itemPedido
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalItemPedido = false;
                        this.loadItemPedido()
                        this.pedido.valor_total = res.data.data.valor_total;
                        store.alerts.push({text: 'Itens do pedido incluídos com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltrosItemPedido() {
                this.temporaryFiltersItemPedido = JSON.parse(JSON.stringify(this.filtersItemPedido))
                this.modalFiltroItemPedido = true
            },
            filtrarItemPedido() {
                this.page = 1
                this.modalFiltroItemPedido = false
                this.filtersItemPedido = JSON.parse(JSON.stringify(this.temporaryFiltersItemPedido))
                this.loadItemPedido()
            },
            limparFiltrosItemPedido() {
                Object.keys(this.temporaryFiltersItemPedido).forEach(k => {
                    this.temporaryFiltersItemPedido[k] = null
                    this.loadItemPedido()
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
            },
            pageItemPedido() {
                this.loadItemPedido()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
            totalFiltersItemPedido() {
                return Object.keys(this.filtersItemPedido).filter(k => this.filtersItemPedido[k] && this.filtersItemPedido[k] != '').length
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
                    {value: 2, text: 'Item' + (this.isItemPedidoChanged ? ' •' : ''), active: this.pedido_id === null ? this.activeTab == 1 : this.activeTab == 2},
                ]
            },
            precoUnitario() {
                const produtoSelecionado = this.produtos.find(produto => produto.id === this.itemPedido.produto_id);
                if (!this.precoManual && produtoSelecionado) {
                    this.itemPedido.preco_unitario = produtoSelecionado.preco_unitario;
                    return produtoSelecionado.preco_unitario;
                }
                return null;
            }
        //     precoUnitario() {
        //     const produtoSelecionado = this.produtos.find(produto => produto.id === this.itemPedido.produto_id);
        //     if (produtoSelecionado && this.itemPedido.preco_unitario === null) {
        //         return produtoSelecionado.preco_unitario;
        //     }
        //     return this.itemPedido.preco_unitario || null;
        // }
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
