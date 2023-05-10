@extends('layouts.main')

@section('title', 'Clientes')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > Clientes" title="Clientes">
        <template slot="icons">
            <i @click="novoCliente" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>

        <c-list :items="clientes" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column">
                        <div>
                            <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="editarCliente(item.data)" class="fas fa-pen"></i>
                        <i @click="idClienteExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

    <c-modal title="Clientes" v-model="modalCliente" size="md">

        <template v-slot:buttons>
            <button @click="salvarCliente">Salvar</button>
        </template>

        <b-row>
            <b-col>

                <b-form-group label-size="sm" label="Nome" label-for="nome">
                    <b-form-input
                        id="nome"
                        v-model="cliente.nome"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="Telefone" label-for="telefone">
                    <b-form-input
                        id="nome"
                        v-model="cliente.telefone"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="Endereco" label-for="endereco">
                    <b-form-input
                        id="nome"
                        v-model="cliente.endereco"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="E-mail" label-for="email">
                    <b-form-input
                        id="nome"
                        v-model="cliente.email"
                        type="text"
                    ></b-form-input>
                </b-form-group>
                <b-form-group label-size="sm" label="CPF/CNPJ" label-for="cpf_cnpj">
                    <b-form-input
                        id="nome"
                        v-model="cliente.cpf_cnpj"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarClientes">Filtrar</button>
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

    <c-modal v-model="excluirModal" title="Excluir Cliente" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirCliente" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o cliente?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                clientes: [],
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalCliente: false,
                modalFiltro: false,
                cliente: {},
                commonFields: [
                    {name: 'Nome', attribute: 'nome'},
                    {name: 'E-mail', attribute: 'email'},
                    {name: 'Telefone', attribute: 'telefone'},
                    {name: 'Endereço', attribute: 'endereco'},
                    {name: 'CPF/CNPJ', attribute: 'cpf_cnpj'}
                ],
                excluirModal: false,
                idClienteExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                }
            }
        },
        methods: {
            loadCliente() {

                axios.get('api/cliente', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'nome',
                        ...this.filters
                    }
                }).then(res => {

                    this.clientes = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoCliente() {
                this.cliente = {
                    nome: null
                }
                this.modalCliente = true
            },
            editarCliente(Cliente) {
                this.cliente = JSON.parse(JSON.stringify(Cliente))
                this.modalCliente = true
            },
            excluirCliente() {

                axios.delete('api/cliente/' + this.idClienteExcluir).then(res => {
                    this.excluirModal = false
                    this.loadCliente()

                    if(res.data.data){
                        store.alerts.push({text: 'Cliente excluído com sucesso', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir Cliente', variant:'danger'})
                    }
                })

            },
            salvarCliente() {

                if (this.cliente.id) {
                    axios.put('api/cliente/' + this.cliente.id, {
                        ...this.cliente
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalCliente = false
                        this.loadCliente()
                        store.alerts.push({text: 'Cliente alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/cliente', {
                        ...this.cliente
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalCliente = false
                        this.loadCliente()
                        store.alerts.push({text: 'Clientes incluído com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarClientes() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadCliente()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadCliente()
                })
            }
        },
        watch: {
            page() {
                this.loadCliente()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            }
        },
        created() {
            this.loadCliente()
        },
        template: `@yield('interface')`
    })

</script>
@endpush
