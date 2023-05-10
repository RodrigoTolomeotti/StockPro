@extends('layouts.main')

@section('title', 'Tipos de Produtos')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Tipos de Produtos" title="Tipos de Produtos">
        <template slot="icons">
            <i @click="novoTipoProduto" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>

        <c-list :items="tiposProdutos" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column">
                        <div>
                            <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="editarTipoProduto(item.data)" class="fas fa-pen"></i>
                        <i @click="idTipoProdutoExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

    <c-modal title="Tipo de Produtos" v-model="modalTipoProduto" size="md">

        <template v-slot:buttons>
            <button @click="salvarTipoProduto">Salvar</button>
        </template>

        <b-row>
            <b-col>

                <b-form-group label-size="sm" label="Nome" label-for="nome">
                    <b-form-input
                        id="nome"
                        v-model="tipoProduto.nome"
                        type="text"
                    ></b-form-input>
                </b-form-group>

            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarContatos">Filtrar</button>
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

    <c-modal v-model="excluirModal" title="Excluir Tipo de Produto" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirTipoProduto" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o contato?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                tiposProdutos: [],
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalTipoProduto: false,
                modalFiltro: false,
                tipoProduto: {},
                commonFields: [
                    {name: 'Nome', attribute: 'nome'}
                ],
                excluirModal: false,
                idTipoProdutoExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                }
            }
        },
        methods: {
            loadtipoProduto() {

                axios.get('api/tipo-produto', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'nome',
                        ...this.filters
                    }
                }).then(res => {

                    this.tiposProdutos = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoTipoProduto() {
                this.tipoProduto = {
                    nome: null
                }
                this.modalTipoProduto = true
            },
            editarTipoProduto(tipoProduto) {
                this.tipoProduto = JSON.parse(JSON.stringify(tipoProduto))
                this.modalTipoProduto = true
            },
            excluirTipoProduto() {

                axios.delete('api/tipo-produto/' + this.idTipoProdutoExcluir).then(res => {
                    this.excluirModal = false
                    this.loadtipoProduto()

                    if(res.data.data){
                        store.alerts.push({text: 'Tipo de Produto excluído com sucesso', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir Tipo de Produto', variant:'danger'})
                    }
                })

            },
            salvarTipoProduto() {

                if (this.tipoProduto.id) {
                    axios.put('api/tipo-produto/' + this.tipoProduto.id, {
                        ...this.tipoProduto
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalTipoProduto = false
                        this.loadtipoProduto()
                        store.alerts.push({text: 'Tipo de Produto alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/tipo-produto', {
                        ...this.tipoProduto
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalTipoProduto = false
                        this.loadtipoProduto()
                        store.alerts.push({text: 'Tipo de Produtos incluído com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarContatos() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadtipoProduto()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadtipoProduto()
                })
            }
        },
        watch: {
            page() {
                this.loadtipoProduto()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            }
        },
        created() {
            this.loadtipoProduto()
        },
        template: `@yield('interface')`
    })

</script>
@endpush
