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
                    <div class="d-flex flex-column">
                        <div>
                            <!-- <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span> -->
                        </div>
                        <div class="d-flex">
                            <!-- <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f">@{{item.data.quantidade}}</span> -->
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="idPedidoExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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
    
    <c-modal title="Pedidos" v-model="modalPedido" size="xl">
        <template v-slot:buttons>
            <button @click="salvarPedido">Salvar</button>
        </template>
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
                    <b-col cols="4">
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
                                v-model="pedido[field.attribute]"
                                v-validate="'nullable'">
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

            </div>
            <div v-if="tabs[1].active" class="p-3">

                teste

            <!-- <b-btn @click="salvarUsuario" class="float-right" variant="primary">Salvar</b-btn> -->

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
                excluirModal: false,
                idPedidoExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                },
                tipoPedido: [
                    {name: 'Entrada', value: 1},
                    {name: 'Saída', value: 2},
                ]
            }
        },
        methods: {
            loadPedido() {

                axios.get('api/pedido', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'pedido.data_atualizacao',
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
                    usuario_id: null,
                    cliente_id: null,
                    valor_total: null,
                    data_liberacao: null,
                    data_entrega: null,
                }
                this.modalPedido = true
            },
            editarPedido(pedido) {
                this.pedido = JSON.parse(JSON.stringify(pedido))
                this.modalPedido = true
            },
            excluirTipoProduto() {

                axios.delete('api/pedido/' + this.idEstoqueExcluir).then(res => {
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
                        this.modalPedido = false
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
                        this.modalPedido = false
                        this.loadPedido()
                        store.alerts.push({text: 'Pedidos incluído com sucesso', variant:'success'})
                    })
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
            isUsuarioChanged() {
                return true
            },
            tabs() {
                return [
                    {value: 1, text: 'Cabeçalho' + (this.isUsuarioChanged ? ' •' : ''), active: this.activeTab == 1},
                    {value: 2, text: 'Item' + (this.isUsuarioChanged ? ' •' : ''), active: this.activeTab == 2},
                ]
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
