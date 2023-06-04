@extends('layouts.main')

@section('title', 'Produtos')

@include('components.c-list')
@include('components.c-card')

@section('interface')
<div class="d-flex flex-column flex-grow-1">
    <c-card reference="StockPro > produtos" title="produtos">
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
                    <b-form-select
                        v-if="field.attribute === 'tipo_produto_id'"
                        id="tipo_produto_id"
                        v-model="produto['tipo_produto_id']"
                        :options="optionsTiposProdutos">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-select
                        v-else-if="field.attribute === 'fornecedor_id'"
                        id="fornecedor_id"
                        v-model="produto['fornecedor_id']"
                        :options="optionsFornecedores">
                        <template v-slot:first>
                            <option :value="null">Selecione um</option>
                        </template>
                    </b-form-select>
                    <b-form-input
                        v-else-if="field.attribute === 'preco_unitario'"
                        id="field.attribute"
                        v-model="produto[field.attribute]"
                        :maxlength="18"
                        type="number"
                    ></b-form-input>
                    <b-form-input
                        v-else-if="field.attribute === 'quantidade'"
                        id="field.attribute"
                        v-model="produto[field.attribute]"
                        :maxlength="18"
                        type="number"
                        readonly="true"
                    ></b-form-input>
                    <b-form-input
                        v-else
                        id="field.attribute"
                        v-model="produto[field.attribute]"
                        type="text"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col cols="6">                
                <div style="padding: .5em;background: #f5f7fb;">
                    <b-form-group label-size="sm">
                        <b-input-group>
                        <div class="product-image"
                            :class="{'product-image-default': !imagem}"
                            :style="imageStyleProduto">
                            <span v-if="!imagem" class="product-image-letter">@{{produto.nome ? produto.nome[0].toUpperCase() : 'Novo Produto'}}</span>
                            <span v-if="produto.id" @click="$refs.imagem.click()" class="product-image-edit"><i class="fas fa-pen"></i></span>
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
                    {name: 'Quantidade Disponível', attribute: 'quantidade'},
                    {name: 'Tipo de Produto', attribute: 'tipo_produto_id'},
                    {name: 'Fornecedor', attribute: 'fornecedor_id'},
                    {name: 'Descrição', attribute: 'descricao'}
                ],
                excluirModal: false,
                idProdutoExcluir: false,
                temporaryFilters: {},
                filters: {
                    nome: null
                },
                imagem: null,
                tipos_produtos: [],
                fornecedores: []
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
                    this.loadProduto()
                })

            },
            throwErrorsProduto(errors) {
                Object.keys(errors).reverse().forEach(k => {
                    errors[k].reverse().forEach(err => {
                        store.alerts.push({text: err, variant: 'danger'})
                    })
                }, 5000);
            },
            loadTiposProdutos() {
                axios.get('api/tipo-produto').then(res => {
                    this.tipos_produtos = res.data.data;
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
                this.loadProduto()
            }
        },
        computed: {
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
            imageStyleProduto() {
                return {
                    'background-image': this.imagem ? 'url(\'' + store.baseUrl + '/users/products/' + this.imagem + '\')' : 'none'
                }
            },
            optionsTiposProdutos() {
                return this.tipos_produtos.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsFornecedores() {
                return this.fornecedores.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            }
        },
        created() {
            this.loadProduto(),
            this.loadTiposProdutos(),
            this.loadFornecedores()
        },
        template: `@yield('interface')`
    })

</script>
@endpush

@push('styles')
<style>
.product-image {
    width: 100%;
    height: 410px;
    border-radius: 2%;
    box-shadow: 0 10px 15px rgba(0,0,0,0.3);
    position: relative;
    background-size: cover;
}

.product-image-default {
    background: #65a2ff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image-default .product-image-letter {
    color: #fff;
    font-size: 3em;
}

.product-image .product-image-edit {
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

.product-image .product-image-edit:hover {
    background: #222;
}

.product-image input {
    display: none;
}
</style>
