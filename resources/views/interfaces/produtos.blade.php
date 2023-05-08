@extends('layouts.main')

@section('title', 'Produtos')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="Climb > produtos" title="produtos">
        <template slot="icons">
            <i @click="novoProduto" class="fas fa-plus fa-lg"></i>
            <i @click="abrirFiltros" class="fas fa-filter fa-lg">
                <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
            </i>
        </template>

        <c-list :items="produtos" :loading="loading" class="mb-3">
            <template v-slot:items="item">
                <div class="d-flex justify-content-between w-100">
                    <div class="d-flex flex-column">
                        <div>
                            <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                        </div>
                    </div>
                    <div class="list-icons">
                        <i @click="editarProduto(item.data)" class="fas fa-pen"></i>
                        <i @click="idProdutoExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

    <c-modal title="Produtos" v-model="modalProduto" size="xl">

        <template v-slot:buttons>
            <button @click="salvarProduto">Salvar</button>
        </template>

        <b-row>
            <b-col cols="6">
                <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                    <b-form-input
                        v-if="field.attribute != 'preco_unitario'"
                        id="field.attribute"
                        v-model="produto[field.attribute]"
                        type="text"
                    ></b-form-input>
                    <b-form-input
                        v-if="field.attribute == 'preco_unitario'"
                        id="field.attribute"
                        v-model="produto[field.attribute]"
                        :maxlength="18"
                        @keypress="isNumber($event)"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col cols="6">                
                <div style="padding: 20px;background: #f5f7fb;">
                    <b-form-group label-size="sm">
                        <b-input-group>
                        <div class="user-image"
                            :class="{'user-image-default': !imagem}"
                            :style="imageStyleProduto">
                            <span v-if="!imagem" class="user-image-letter">@{{produto.nome ? produto.nome[0].toUpperCase() : 'Novo Produto'}}</span>
                            <span @click="$refs.imagem.click()" class="user-image-edit"><i class="fas fa-pen"></i></span>
                            <input ref="imagem" type="file" accept="image/png,image/jpeg" @change="uploadImagemProduto($event)">
                        </div>
                        </b-input-group>
                    </b-form-group>
                </div>
            </b-col>
        </b-row>

    </c-modal>

    <c-modal title="Filtro" v-model="modalFiltro" size="md">

        <template v-slot:buttons>
            <button @click="limparFiltros">Limpar</button>
            <button @click="filtrarProdutos">Filtrar</button>
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

    <c-modal v-model="excluirModal" title="Excluir Produto" size="md">

        <template v-slot:buttons>
            <button @click="excluirModal = false" size="sm">Não</button>
            <button @click="excluirProduto" size="sm" variant="primary">Sim</button>
        </template>

        Deseja realmente excluir o produto?

    </c-modal>
</div>
@endsection
@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                produtos: [],
                limit: 15,
                page: 1,
                totalRows: 0,
                loading: false,
                modalProduto: false,
                modalFiltro: false,
                produto: {},
                commonFields: [
                    {name: 'Nome', attribute: 'nome'},
                    {name: 'Custo', attribute: 'custo'},
                    {name: 'Preço Unitário', attribute: 'preco_unitario'},
                    {name: 'Tipo de Produto', attribute: 'tipo_produto_id'},
                    {name: 'Descrição', attribute: 'descricao'}
                ],
                excluirModal: false,
                idProdutoExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                },
                imagem: null,
            }
        },
        methods: {
            loadProduto() {

                axios.get('api/produto', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'nome',
                        ...this.filters
                    }
                }).then(res => {

                    this.produtos = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                })
            },
            novoProduto() {
                this.produto = {
                    nome: null,
                    custo: null,
                    preco_unitario: null,
                    descricao: null,
                    imagem: null
                }
                this.imagem = null;
                this.modalProduto = true
            },
            editarProduto(Produto) {
                this.produto = JSON.parse(JSON.stringify(Produto))
                this.imagem =this.produto.imagem;
                this.modalProduto = true
            },
            excluirProduto() {

                axios.delete('api/produto/' + this.idProdutoExcluir).then(res => {
                    this.excluirModal = false
                    this.loadProduto()

                    if(res.data.data){
                        store.alerts.push({text: 'Produto excluído com sucesso', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir Produto', variant:'danger'})
                    }
                })

            },
            salvarProduto() {
                if (this.produto.id) {
                    axios.put('api/produto/' + this.produto.id, {
                        ...this.produto
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalProduto = false
                        this.loadProduto()
                        store.alerts.push({text: 'Produto alterado com sucesso', variant:'success'})
                    })

                } else {
                    axios.post('api/produto', {
                        ...this.produto
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }
                        this.modalProduto = false
                        this.loadProduto()
                        store.alerts.push({text: 'Produtos incluído com sucesso', variant:'success'})
                    })
                }
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarProdutos() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadProduto()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                    this.loadProduto()
                })
            },
            uploadImagemProduto(event) {

                var formData = new FormData()

                formData.append("imagem", event.target.files[0]);
                formData.append("id", this.produto.id);

                axios.post(store.apiUrl + '/produto/imagem', formData, {
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    if (res.data.errors) {
                        this.throwErrorsProduto(res.data.errors);
                        return;
                    }
                    this.$refs.imagem.value = ''
                    this.imagem = res.data.imagem + '?' + Math.random()
                    if(this.produto.id && this.imagem) {
                        this.produto.imagem = this.imagem;
                    }
                    store.alerts.push({text:'Imagem alterada com sucesso', variant:'success'})
                })

            },
            throwErrorsProduto(errors) {
                Object.keys(errors).reverse().forEach(k => {
                    errors[k].reverse().forEach(err => {
                        store.alerts.push({text: err, variant: 'danger'})
                    })
                }, 5000)
            }
        },
        watch: {
            page() {
                this.loadProduto()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
            imageStyleProduto() {
                return {
                    'background-image': this.imagem ? 'url(\'' + store.baseUrl + '/users/products' + this.imagem + '\')' : 'none'
                }
            },
        },
        created() {
            this.loadProduto()
        },
        template: `@yield('interface')`
    })

</script>
@endpush

@push('styles')
<style>
.user-image {
    width: 100%;
    height: 410px;
    border-radius: 2%;
    box-shadow: 0 10px 15px rgba(0,0,0,0.3);
    position: relative;
    background-size: cover;
}

.user-image-default {
    background: #65a2ff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-image-default .user-image-letter {
    color: #fff;
    font-size: 3em;
}

.user-image .user-image-edit {
    width: 50px;
    height: 50px;
    background: #353535;
    color: #fff;
    position: absolute;
    top: 5;
    right: 5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    cursor: pointer;
    transition: .2s;
}

.user-image .user-image-edit:hover {
    background: #222;
}

.user-image input {
    display: none;
}
</style>
