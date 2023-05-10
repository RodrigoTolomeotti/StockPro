@extends('layouts.main')

@section('title', 'Fornecedores')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Fornecedores" title="Fornecedores">
        <template slot="icons">
            <i @click="novoFornecedor" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>

        <c-list :items="fornecedores" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column">
                        <div>
                            <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="editarFornecedor(item.data)" class="fas fa-pen"></i>
                        <i @click="idFornecedorExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

    <c-modal title="Fornecedores" v-model="modalFornecedor" size="md">

        <template v-slot:buttons>
            <button @click="salvarFornecedor">Salvar</button>
        </template>

        <b-row>
            <b-col>

                <b-form-group label-size="sm" label="Nome" label-for="nome">
                    <b-form-input
                        id="nome"
                        v-model="fornecedor.nome"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="Telefone" label-for="telefone">
                    <b-form-input
                        id="nome"
                        v-model="fornecedor.telefone"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="Endereco" label-for="endereco">
                    <b-form-input
                        id="nome"
                        v-model="fornecedor.endereco"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="E-mail" label-for="email">
                    <b-form-input
                        id="nome"
                        v-model="fornecedor.email"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="CPF/CNPJ" label-for="cpf_cnpj">
                    <b-form-input
                        id="nome"
                        v-model="fornecedor.cpf_cnpj"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarFornecedores">Filtrar</button>
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

    <c-modal v-model="excluirModal" title="Excluir Fornecedor" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirFornecedor" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o fornecedor?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                fornecedores: [],
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalFornecedor: false,
                modalFiltro: false,
                fornecedor: {},
                commonFields: [
                    {name: 'Nome', attribute: 'nome'},
                    {name: 'E-mail', attribute: 'email'},
                    {name: 'Telefone', attribute: 'telefone'},
                    {name: 'Endereço', attribute: 'endereco'},
                    {name: 'CPF/CNPJ', attribute: 'cpf_cnpj'}
                ],
                excluirModal: false,
                idFornecedorExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                }
            }
        },
        methods: {
            loadFornecedor() {

                axios.get('api/fornecedor', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'nome',
                        ...this.filters
                    }
                }).then(res => {

                    this.fornecedores = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoFornecedor() {
                this.fornecedor = {
                    nome: null
                }
                this.modalFornecedor = true
            },
            editarFornecedor(Fornecedor) {
                this.fornecedor = JSON.parse(JSON.stringify(Fornecedor))
                this.modalFornecedor = true
            },
            excluirFornecedor() {

                axios.delete('api/fornecedor/' + this.idFornecedorExcluir).then(res => {
                    this.excluirModal = false
                    this.loadFornecedor()

                    if(res.data.data){
                        store.alerts.push({text: 'Fornecedor excluído com sucesso', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir Fornecedor', variant:'danger'})
                    }
                })

            },
            salvarFornecedor() {

                if (this.fornecedor.id) {
                    axios.put('api/fornecedor/' + this.fornecedor.id, {
                        ...this.fornecedor
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalFornecedor = false
                        this.loadFornecedor()
                        store.alerts.push({text: 'Fornecedor alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/fornecedor', {
                        ...this.fornecedor
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalFornecedor = false
                        this.loadFornecedor()
                        store.alerts.push({text: 'Fornecedor incluído com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarFornecedores() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadFornecedor()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadFornecedor()
                })
            }
        },
        watch: {
            page() {
                this.loadFornecedor()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            }
        },
        created() {
            this.loadFornecedor()
        },
        template: `@yield('interface')`
    })

</script>
@endpush
