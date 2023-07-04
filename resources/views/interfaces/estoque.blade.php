@extends('layouts.main')

@section('title', 'Estoque')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Estoque" title="Estoque">
        <template slot="icons">
            <i @click="novoEstoque" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>

        <c-list :items="estoques" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column">
                        <div>
                            <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                        </div>
                        <div class="d-flex">
                            <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f">@{{item.data.quantidade}}</span>
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="idEstoqueExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

    <c-modal title="Estoques" v-model="modalEstoque" size="md">

        <template v-slot:buttons>
            <button @click="salvarEstoque">Salvar</button>
        </template>

        <b-row>
            <b-col cols="12">
                <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                    <b-form-select
                        v-if="field.attribute === 'produto_id'"
                        id="produto_id"
                        v-model="estoque['produto_id']"
                        :options="optionsProdutos">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-select
                        v-else-if="field.attribute === 'fornecedor_id'"
                        id="fornecedor_id"
                        v-model="estoque['fornecedor_id']"
                        :options="optionsFornecedores">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-select
                        v-else-if="field.attribute === 'tipo_estoque'"
                        id="id"
                        v-model="estoque['tipo_estoque']"
                        :options="optionsTipoEstoque">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-input
                        v-else
                        id="field.attribute"
                        v-model="estoque[field.attribute]"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarEstoque">Filtrar</button>
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

    <c-modal v-model="excluirModal" title="Excluir Estoque" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirTipoProduto" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o estoque?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                produtos: [],
                produto: {},
                estoques: {},
                estoque: [],
                fornecedores: [],
                fornecedor: {},
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalEstoque: false,
                modalFiltro: false,
                tipoProduto: {},
                commonFields: [
                    {name: 'Produto', attribute: 'produto_id'},
                    {name: 'Custo', attribute: 'custo'},
                    {name: 'Fornecedor', attribute: 'fornecedor_id'},
                    {name: 'Quantidade', attribute: 'quantidade'},
                    {name: 'Tipo Estoque', attribute: 'tipo_estoque'},
                ],
                excluirModal: false,
                idEstoqueExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                },
                tipoEstoque: [
                    {name: 'Entrada', value: 1},
                    {name: 'Saída', value: 2},
                ]
            }
        },
        methods: {
            loadEstoque() {

                axios.get('api/estoque', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'estoque.data_atualizacao',
                        ...this.filters
                    }
                }).then(res => {

                    this.estoques = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoEstoque() {
                this.estoque = {
                    produto_id: null,
                    custo: null,
                    fornecedor_id: null,
                    quantidade: null,
                    tipo_estoque: null
                }
                this.modalEstoque = true
            },
            editarEstoque(estoque) {
                this.estoque = JSON.parse(JSON.stringify(estoque))
                this.modalEstoque = true
            },
            excluirTipoProduto() {

                axios.delete('api/estoque/' + this.idEstoqueExcluir).then(res => {
                    if (res.data.errors) {
                        Object.keys(res.data.errors).forEach(k => {
                            res.data.errors[k].forEach(e => {
                                store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                            })
                        })
                        return;
                    }
                    this.excluirModal = false
                    this.loadEstoque()

                    if(res.data.data){
                        store.alerts.push({text: 'Estoque excluído com sucesso', variant:'success'})
                    }   
                })

            },
            salvarEstoque() {

                if (this.estoque.id) {
                    axios.put('api/estoque/' + this.estoque.id, {
                        ...this.estoque
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalEstoque = false
                        this.loadEstoque()
                        store.alerts.push({text: 'Estoque alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/estoque', {
                        ...this.estoque
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalEstoque = false
                        this.loadEstoque()
                        store.alerts.push({text: 'Estoques incluído com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarEstoque() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadEstoque()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadEstoque()
                })
            },
            loadProdutos() {
                axios.get('api/produto').then(res => {
                    this.produtos = res.data.data;
                })
            },
            loadFornecedores() {
                axios.get('api/fornecedor').then(res => {
                    this.fornecedores = res.data.data;
                })
            }
        },
        watch: {
            page() {
                this.loadEstoque()
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
            optionsFornecedores() {
                return this.fornecedores.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsTipoEstoque() {
                return this.tipoEstoque.map(o => ({
                    value: o.value,
                    text: o.name
                }))
            }
        },
        created() {
            this.loadEstoque(),
            this.loadProdutos(),
            this.loadFornecedores()
            
        },
        template: `@yield('interface')`
    })

</script>
@endpush
