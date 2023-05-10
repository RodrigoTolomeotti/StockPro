@extends('layouts.main')

@section('title', 'Contatos')

@include('components.c-list')
@include('components.c-card')
@include('components.c-multiselect')

@section('interface')
    <div class="d-flex flex-column flex-grow-1">

        <c-card reference="StockPro > Contatos" title="Contatos">

            <template slot="icons">
                <i @click="modalExportar = true" class="fa fa-file-export fa-lg" title="Exportar contatos"></i>
                <i @click="modalCSV = true" class="fas fa-file-csv fa-lg"title="Importar contatos"></i>
                <i @click="novoContato" class="fas fa-plus fa-lg" title="Novo contato"></i>
                <i @click="abrirFiltros" class="fas fa-filter fa-lg" title="Abrir filtros">
                    <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
                </i>
            </template>

            <c-list :items="contatos" :loading="loading" class="mb-3">
                <template v-slot:items="item">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex flex-column">
                            <div>
                                <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                                <span v-if="item.data.empresa" style="font-size: .9em; color: #222;">da <b>@{{item.data.empresa}}</b></span>
                            </div>
                            <span style="font-size: .8em; color: #6f6f6f">@{{item.data.email}}</span>
                        </div>
                        <div class="list-icons">
                            <i v-if="item.data.bloqueios_ativos != 0" @click="listarBloqueios(item.data.id)" style="color:#ff6565" class="fas fa-ban" title="Ver bloqueios"></i>
                            <i v-else-if="item.data.bloqueios_inativos != 0" @click="listarBloqueios(item.data.id)" class="fas fa-ban" title="Ver bloqueios"></i>
                            <i @click="editarContato(item.data)" class="fas fa-pen"></i>
                            <i @click="idContatoExcluir = item.data.id; excluirModal = true" class="fas fa-trash-alt"></i>
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

        <c-modal title="Contato" v-model="modalContato" size="full">

            <template v-slot:buttons>
                <button @click="salvarContato">Salvar</button>
            </template>

            <b-row>
                <b-col>

                    <b-form-group label-size="sm" v-for="field, i in commonFields" :key="i" :label="field.name" :label-for="field.attribute">
                        <b-form-input
                            v-if="field.attribute != 'cpf_cnpj'"
                            id="field.attribute"
                            v-model="contato[field.attribute]"
                            type="text"
                        ></b-form-input>
                        <b-form-input
                            v-if="field.attribute == 'cpf_cnpj'"
                            id="field.attribute"
                            v-model="contato[field.attribute]"
                            v-on:input="formatarCpfCnpj()"
                            :maxlength="18"
                            @keypress="isNumber($event)"
                            type="text"
                        ></b-form-input>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Cargo" label-for="cargo_id">
                        <b-form-select
                            id="cargo_id"
                            v-model="contato['cargo_id']"
                            :options="optionsCargo"
                            class="mb-3">

                            <template v-slot:first>
                                <option :value="null">Selecione um</option>
                            </template>

                        </b-form-select>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Departamento" label-for="departamento_id">
                        <b-form-select
                            id="departamento_id"
                            v-model="contato['departamento_id']"
                            :options="optionsDepartamento"
                            class="mb-3">

                            <template v-slot:first>
                                <option :value="null">Selecione um</option>
                            </template>

                        </b-form-select>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Profissão" label-for="profissao_id">
                        <b-form-select
                            id="profissao_id"
                            v-model="contato['profissao_id']"
                            :options="optionsProfissao"
                            class="mb-3">

                            <template v-slot:first>
                                <option :value="null">Selecione um</option>
                            </template>

                        </b-form-select>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Grupo de Contato" label-for="grupo_contato_id">
                        <b-form-select
                            id="grupo_contato_id"
                            v-model="contato['grupo_contato_id']"
                            :options="optionsGrupoContato"
                            class="mb-3">

                            <template v-slot:first>
                                <option :value="null">Selecione um</option>
                            </template>

                        </b-form-select>
                    </b-form-group>

                </b-col>
                <b-col cols="4">
                    <div style="padding: 20px;background: #f5f7fb;">

                        <span class="d-block mb-4">Redes Sociais</span>

                        <b-form-group label-size="sm" v-for="field, i in networkFields" :key="i" :label="field.name" :label-for="field.attribute">

                            <b-input-group>

                                <template v-slot:prepend>
                                    <b-input-group-text>
                                        <i :class="field.icon"></i>
                                    </b-input-group-text>
                                </template>

                                <b-form-input
                                    :id="field.attribute"
                                    v-model="contato[field.attribute]"
                                    type="text"
                                ></b-form-input>

                            </b-input-group>

                        </b-form-group>
                    </div>
                </b-col>
            </b-row>

        </c-modal>

        <c-modal title="Filtro" v-model="modalFiltro" size="xl">

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

                    <b-form-group label-size="sm" label="E-mail" label-for="filtro-email">
                        <b-form-input
                            id="filtro-email"
                            v-model="temporaryFilters.email"
                            type="text"
                        ></b-form-input>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Cargo" label-for="filtro-cargo">
                            <c-multiselect
                            id="filtro-cargo"
                            v-model="temporaryFilters.cargos"
                            :options="optionsCargo"></c-multiselect>

                            <template v-slot:first>
                                <option :value="null"></option>
                            </template>

                    </b-form-group>

                    <b-form-group label-size="sm" label="Campanha" label-for="filtro-campanha">
                            <c-multiselect
                            id="filtro-campanha"
                            v-model="temporaryFilters.campanhas"
                            :options="optionsCampanha"></c-multiselect>

                    </b-form-group>
                    <b-row>
                        <b-form-group style="padding-left: 20px;" label-size="sm" label="Data Cadastro do Contato:" title="Data de Criação do Contato"></b-form-group>
                    </b-row>

                    <b-row>
                        <b-form-group style="padding-right: 0px; padding-left: 20px; flex-wrap: unset" label-cols="2"  label-size="sm" label="De:" label-for="filtro-data_inicial_criacao" title="Data de Criação do Contato">
                            <b-form-input size="sm" type="date" id="filtro-data_inicial_criacao" v-model="temporaryFilters.data_inicial_criacao" v-validate="'required'"></b-form-input>
                        </b-form-group>

                        <b-form-group style="padding-right: 0px; padding-left: 20px; flex-wrap: unset; " label-cols="2"  label-size="sm" label="Até:" label-for="filtro-data_final_criacao" title="Data de Criação do Contato">
                            <b-form-input size="sm" type="date" id="filtro-data_final_criacao" v-model="temporaryFilters.data_final_criacao" v-validate="'required'"></b-form-input>
                        </b-form-group>
                    </b-row>
                </b-col>

                <b-col>
                    <b-form-group label-size="sm" label="Departamento" label-for="filtro-departamento">
                            <c-multiselect
                            id="filtro-departamento"
                            v-model="temporaryFilters.departamentos"
                            :options="optionsDepartamento"></c-multiselect>

                            <template v-slot:first>
                                <option :value="null"></option>
                            </template>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Profissão" label-for="filtro-profissao">
                            <c-multiselect
                            id="filtro-profissao"
                            v-model="temporaryFilters.profissoes"
                            :options="optionsProfissao"></c-multiselect>

                            <template v-slot:first>
                                <option :value="null"></option>
                            </template>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Grupo de Contato" label-for="filtro-grupo-contato">
                            <c-multiselect
                            id="filtro-grupo-contato"
                            v-model="temporaryFilters.grupos_contatos"
                            :options="optionsGrupoContato"></c-multiselect>

                            <template v-slot:first>
                                <option :value="null"></option>
                            </template>
                    </b-form-group>

                    <b-form-group label-size="sm" label="Situação do Contato" label-for="filtro-exibir-contatos">
                        <b-form-select
                            id="filtro-exibir-contatos"
                            v-model="temporaryFilters.exibir_contatos"
                            :options="optionsExibirContatos">

                            <template v-slot:first>
                                <option :value="null"></option>
                            </template>
                        </b-form-select>
                    </b-form-group>

                </b-col>
            </b-row>
        </c-modal>

        <c-modal v-model="excluirModal" title="Excluir contato" size="md">

            <template v-slot:buttons>
                <button @click="excluirModal = false" size="sm">Não</button>
                <button @click="excluirContato" size="sm" variant="primary">Sim</button>
            </template>

            Deseja realmente excluir o contato?

        </c-modal>

        <c-modal title="Bloqueios" v-model="modalBloqueios" size="full">
            <c-list :items="bloqueios" style="height: calc(100vh - 255px)" class="mb-3">
                <template v-slot:items="item" class="listBounce">

                    <div class="d-flex flex-column w-100">

                        <div class="d-flex w-100 mb-2">
                            <div class="d-flex flex-column">
                                <span
                                :style="{'color': item.data.data_inativacao ? '#bbb7b7' : '#222'}"
                                style="font-size: .9em; font-weight: bold;"
                                >
                                    @{{item.data.motivo_bloqueio}} - Dica: @{{item.data.dica}}
                                </span>
                            </div>
                            <div class="d-flex flex-column ml-auto">
                                <span
                                :style="{'color': item.data.data_inativacao ? '#bbb7b7' : '#222'}"
                                style="font-size: .9em; font-weight: bold;"
                                >
                                    @{{item.data.data_bloqueio | datetime}}
                                </span>
                            </div>
                            <div class="d-flex flex-column ml-auto">
                                <span>
                                    <button @click="exibirBounce(item.data.descricao)" style="margin-top: -5px;" class="btn btn-primary btn-sm rounded-pill">Exibir detalhes técnicos</button>
                                </span>
                            </div>
                            <div class="list-icons">
                                <i v-if="item.data.data_inativacao == null" title="Inativar bloqueio" style="margin-top: 5px;" @click="idBloqueioExcluir = item.data.id; idContatoBloqueioExcluir = item.data.contato_id; excluirBloqueioModal = true" class="fas fa-trash-alt"></i>
                            </div>
                        </div>

                        <!-- <div
                        :style="{'color': item.data.data_inativacao ? '#bbb7b7' : '#6f6f6f'}"
                        class="w-100"
                        style="font-size: .8em;overflow: hidden">
                            @{{item.data.descricao}}
                        </div> -->

                    </div>

                </template>



            </c-list>
            <span class="card-divisor mb-3"></span>

            <div class="d-flex justify-content-between">
                <span style="font-size: .8em;font-weight: bold;">
                    @{{(pageBloqueios - 1) * limitBloqueios + 1}} - @{{pageBloqueios * limitBloqueios > totalRowsBloqueios ? totalRowsBloqueios : pageBloqueios * limitBloqueios}} / @{{totalRowsBloqueios}}
                </span>
                <b-pagination
                    v-model="pageBloqueios"
                    :total-rows="totalRowsBloqueios"
                    :per-page="limitBloqueios"
                    aria-controls="my-table"
                    pills
                    size="sm"
                    class="mb-0"
                ></b-pagination>
            </div>
        </c-modal>

        <c-modal v-model="modalBounce" title="Detalhes técnicos" size="lg">

            @{{ this.emailBounce }}

        </c-modal>

        <c-modal v-model="excluirBloqueioModal" title="Inativar bloqueio" size="md">

            <template v-slot:buttons>
                <button @click="excluirBloqueioModal = false" size="sm">Não</button>
                <button @click="excluirBloqueio" size="sm" variant="primary">Sim</button>
            </template>

            Deseja realmente inativar o bloqueio?

        </c-modal>

        <c-modal v-model="modalCSV" title="Importação de planilha CSV" size="md">
            <template v-slot:buttons>
                <button @click="importCSV" size="sm">Importar</button>
            </template>
            <div class="" style="font-size: 1em">
                <p>Antes de realizar a importação da planilha CSV, leia o manual de instruções<p>

                <div class="text-center mb-3">
                    <b-button size="sm" pill variant="primary" @click="modalInstrucoes = true">Manual de Instruções</b-button>
                </div>

            </div>
            <b-form-file
              v-model="fileCsv"
              placeholder="Escolha ou arraste o arquivo..."
              drop-placeholder="Drop file here..."
              class="mt-3"
              accept=".csv"
              browse-text="Procurar"
            ></b-form-file>
        </c-modal>

        <c-modal v-model="modalExportar" title="Exportar" size="md">
        <div class="text-center mb-3">
            <b-form-group label-size="lg" label="Opções de Exportação:" label-for="opcoes-exportacao"></b-form-group>
                    <button class="btn btn-primary btn-md mt-1 rounded-pill" @click="exportarContatos()">Padrão .CSV</button><br>
                    <button class="btn btn-primary btn-md mt-1 rounded-pill" @click="exportarContatosSpotter()">Spotter .XLSX</button>
        </div>
        </c-modal>

        <c-modal v-model="modalInstrucoes" title="Manual de instruções de importação" size="lg">
            <b-row>1️⃣ A primeira linha (cabeçalho) do arquivo CSV é desconsiderada.</b-row>
            <b-row>2️⃣ Certifique-se que as colunas estejam conforme a sequencia:</b-row>
            <b-row>
                <b-col cols="3" v-for="campo, i in colunas">
                    @{{i+1}} - @{{campo.text}}
                    <small style="font-size: 1em; color: red"><b>@{{campo.required ? '*' : ''}}</b></small>
                </b-col>
            </b-row>
            <b-row>
                <p class="mb-0">3️⃣ Os campos com <b style="color:red">*</b> são obrigatórios.</p>
            </b-row>
            <b-row>4️⃣ Os campos Cargo, Departamento, Profissão e Grupo de Contato devem ter o mesmo nome dos campos do sistema Climb.</b-row>
            <b-row>
                <p class="mb-0">5️⃣ Delimitador de campo do arquivo CSV: Vírgula ( <b>,</b> )</p>
            </b-row>
            <b-row>
                <p class="mb-0">6️⃣ Delimitador de texto do arquivo CSV: Aspas ( <b>"</b> )</p>
            </b-row>
        </c-modal>

        <c-modal v-model="modalErros" title="Erros de importação" size="lg">
            <div ref="list" class="list pt-0 pr-3 pb-3 pl-3 flex-grow-1">

                <div class="list-item d-flex" v-for="item, index in errorsContatos">
                    <slot name="items" :data="item, index">
                        <span style="font-size: .9em; color: #222;"><b>Linha @{{item.linha}}</b> @{{item.errors}}</span>
                    </slot>
                </div>
            </div>
        </c-modal>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('interface', {
        data() {
            return {
                contatos: [],
                bloqueios: [],
                cargos: [],
                departamentos: [],
                profissoes: [],
                grupos_contatos: [],
                campanhas: [],
                errorsContatos: [],
                limit: 15,
                limitBloqueios: 15,
                page: 1,
                pageBloqueios: 1,
                totalRows: 0,
                totalRowsBloqueios: 0,
                loading: false,
                modalContato: false,
                modalFiltro: false,
                modalBloqueios: false,
                modalCSV: false,
                modalExportar: false,
                modalErros: false,
                modalInstrucoes: false,
                modalBounce: false,
                emailBounce: null,
                fileCsv: null,
                contato: {},
                commonFields: [
                    {name: 'Nome', attribute: 'nome'},
                    {name: 'Empresa', attribute: 'empresa'},
                    {name: 'CNPJ/CPF', attribute: 'cpf_cnpj'},
                    {name: 'Email', attribute: 'email'},
                    {name: 'Telefone', attribute: 'telefone'}
                ],
                networkFields: [
                    {name: 'Facebook', attribute: 'facebook_link', icon: 'fab fa-facebook-square'},
                    {name: 'Linkedin', attribute: 'linkedin_link', icon: 'fab fa-linkedin'},
                    {name: 'Instagram', attribute: 'instagram_link', icon: 'fab fa-instagram'},
                    {name: 'Twitter', attribute: 'twitter_link', icon: 'fab fa-twitter-square'},
                ],
                colunas: [
                    {text: 'Nome', required: true},
                    {text: 'Empresa', required: true},
                    {text: 'CPF/CNPJ'},
                    {text: 'Email', required: true},
                    {text: 'Telefone'},
                    {text: 'Cargo'},
                    {text: 'Departamento'},
                    {text: 'Profissão'},
                    {text: 'Grupo Contato'},
                    {text: 'Facebook'},
                    {text: 'Linkedin'},
                    {text: 'Instagram'},
                    {text: 'Twitter'},
                    ],
                excluirModal: false,
                excluirBloqueioModal: false,
                idContatoExcluir: false,
                idBloqueioExcluir: false,
                idContatoBloqueioExcluir: false,
                temporaryFilters: {},
                importacao: [],
                filters: {
                    nome: null,
                    email: null,
                    cargos: [],
                    campanhas: [],
                    departamentos: [],
                    profissoes: [],
                    grupos_contatos: [],
                    bloqueio: null,
                    exibir_contatos: null,
                    data_inicial_criacao: null,
                    data_final_criacao: null
                },
                exibir_contatos: [
                    {value: 'sem-retorno', text: 'Sem Retorno'},
                    {value: 'com-retorno', text: 'Com Retorno'},
                    {value: 'com-bloqueio', text: 'Com Bloqueio'},
                ],
            }
        },
        methods: {
            loadContatos() {

                axios.get('api/contato', {
                    params: {
                        limit: this.limit,
                        offset: this.page * this.limit - this.limit,
                        sort_by: 'nome',
                        ...this.filters
                    }
                }).then(res => {

                    this.contatos = res.data.data

                    this.totalRows = res.data.totalRows

                    this.loading = false

                }).catch((errors) => {

                    store.alerts.push({text: 'Algo deu errado ao carregar contatos 😢', variant:'danger', delay:'3500'})

                })
            },
            loadCargos() {
                axios.get('api/cargo').then(res => {
                    this.cargos = res.data.data
                })
            },
            loadDepartamentos() {
                axios.get('api/departamento').then(res => {
                    this.departamentos = res.data.data
                })
            },
            loadProfissoes() {
                axios.get('api/profissao').then(res => {
                    this.profissoes = res.data.data
                })
            },
            loadGrupoContatos() {
                axios.get('api/grupo-contato').then(res => {
                    this.grupos_contatos = res.data.data
                })
            },
            loadCampanhas() {
                axios.get('api/campanha').then(res => {
                    this.campanhas = res.data.data
                })
            },
            loadBloqueios(contato_id) {
                axios.get('api/contato/bloqueios/' + contato_id, {
                    params: {
                        limit: this.limitBloqueios,
                        offset: this.pageBloqueios * this.limitBloqueios - this.limitBloqueios,
                        sort_by: 'motivo_bloqueio_id',
                        ...this.filters
                    }
                }).then(res => {

                    this.bloqueios = res.data.data

                    this.totalRowsBloqueios = res.data.totalRows

                })
            },
            novoContato() {
                this.contato = {
                    nome: null,
                    empresa: null,
                    cpf_cnpj: null,
                    email: null,
                    telefone: null,
                    cargo_id: null,
                    departamento_id: null,
                    profissao_id: null,
                    grupo_contato_id: null,
                    facebook_link: null,
                    instagram_link: null,
                    linkedin_link: null,
                    twitter_link: null,
                }
                this.modalContato = true
            },
            editarContato(contato) {
                this.contato = JSON.parse(JSON.stringify(contato))
                this.formatarCpfCnpj()
                this.modalContato = true
            },
            listarBloqueios(contato_id){
                this.loadBloqueios(contato_id)
                this.modalBloqueios = true
            },
            salvarContato() {
                if(this.contato.cpf_cnpj){
                    this.contato.cpf_cnpj = this.contato.cpf_cnpj.replace(/(\.|\/|\-)/g,"");
                }

                if (this.contato.id) {

                    axios.put('api/contato/' + this.contato.id, {
                        ...this.contato
                    }).then(res => {
                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    this.formatarCpfCnpj()
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }

                        this.modalContato = false
                        this.loadContatos()
                        store.alerts.push({text: 'Contato alterado com sucesso 👍', variant:'success'})
                    }).catch((errors) => {
                        this.formatarCpfCnpj()
                        store.alerts.push({text: 'Algo deu errado ao salvar contato 😢', variant:'danger', delay:'3500'})

                    })

                } else {

                    axios.post('api/contato', {
                        ...this.contato
                    }).then(res => {

                        if (res.data.errors) {
                            Object.keys(res.data.errors).forEach(k => {
                                res.data.errors[k].forEach(e => {
                                    this.formatarCpfCnpj()
                                    store.alerts.push({text: `${e}`, variant:'danger', delay: 3500})
                                })
                            })
                            return;
                        }

                        this.modalContato = false
                        this.loadContatos()
                        store.alerts.push({text: 'Contato incluído com sucesso 👍', variant:'success'})
                    }).catch((errors) => {
                        this.loadContatos()
                        store.alerts.push({text: 'Algo deu errado ao cadastrar contato 😢', variant:'danger', delay:'3500'})

                    })

                }
            },
            excluirContato() {

                axios.delete('api/contato/' + this.idContatoExcluir).then(res => {
                    this.excluirModal = false
                    this.loadContatos()
                    if(res.data.data){
                        store.alerts.push({text: 'Contato excluido com sucesso 👍', variant:'success'})
                    }else{
                        store.alerts.push({text: 'Erro ao excluir contato', variant:'danger'})
                    }

                }).catch((errors) => {

                    store.alerts.push({text: 'Algo deu errado tente mais tarde 😢', variant:'danger', delay:'3500'})

                })

            },
            excluirBloqueio() {

                axios.delete('api/contato/bloqueios/' + this.idBloqueioExcluir).then(res => {

                    this.excluirBloqueioModal = false
                    this.loadBloqueios(this.idContatoBloqueioExcluir)

                    if(res.data.data){
                        store.alerts.push({text: 'Bloqueio inativado com sucesso 👍', variant:'success'})
                        this.loadContatos();
                    }else{
                        store.alerts.push({text: 'Erro ao inativar bloqueio', variant:'danger'})
                    }

                }).catch((errors) => {

                    store.alerts.push({text: 'Algo deu errado tente mais tarde 😢', variant:'danger', delay:'3500'})

                })

            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarContatos() {
                this.page = 1
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadContatos()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    if (k == 'cargos' || k == 'campanhas' || k == 'departamentos' || k == 'profissoes' || k == 'grupos_contatos'){
                        this.temporaryFilters = {
                            cargos: [],
                            campanhas: [],
                            departamentos: [],
                            profissoes: [],
                            grupos_contatos: [],
                        }
                    }else{
                        this.temporaryFilters[k] = null
                    }
                })
            },
            loadTextFromFile(ev) {
              this.fileCsv = ev.target.files[0];

            },
            importCSV() {
                var data = new FormData()
                data.append('file', this.fileCsv)

                axios.post('api/contato/upload', data, {
                    headers: {'Content-Type': 'multipart/form-data'}
                }).then(res => {

                    this.importacao = res.data.data

                    if(this.importacao.extensao != 'csv') {
                        store.alerts.push({text: 'Arquivo com extensão inválida 😢', variant:'danger', delay:5000})
                        return
                    }

                    if(this.importacao.qtd_errors != 0) {
                            this.errorsContatos = this.importacao.errors
                            this.modalErros = true;
                    } else {
                        if(this.importacao.qtd_inseridos != 0) {
                            store.alerts.push({text: this.importacao.qtd_inseridos + ' Contatos importados com sucesso 👍', variant:'success', delay:5000})
                        }
                        if(this.importacao.qtd_atualizados != 0) {
                            store.alerts.push({text: this.importacao.qtd_atualizados + ' Contatos atualizados com sucesso 👍', variant:'success', delay:5000})
                        }
                    }
                        this.modalCSV = false;
                        this.loadContatos()

                }).catch((errors) => {

                    store.alerts.push({text: 'Algo deu errado tente mais tarde 😢', variant:'danger', delay:'3500'})

                })
            },
            exibirBounce(emailBounce){
                this.emailBounce = emailBounce;
                this.modalBounce = true;
            },
            exportarContatos() {
                axios.get('api/contato/export', {
                    params: {
                        ...this.filters
                    },
                    responseType: 'arraybuffer'
                }).then(res => {
                    if(res){
                        store.alerts.push({text: 'Exportando contatos 👍', variant:'success'})

                        let blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });

                        var downloadLink = document.createElement("a");

                        url = window.URL.createObjectURL(blob);
                        downloadLink.href = url;
                        downloadLink.download = "contatos_climb.csv";

                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);


                    }else{
                        store.alerts.push({text: 'Erro ao exportar contato 😢', variant:'danger'})
                    }

                }).catch((errors) => {

                    store.alerts.push({text: 'Algo deu errado tente mais tarde 😢', variant:'danger', delay:'3500'})

                })
            },

            exportarContatosSpotter() {
                axios.get('api/contato/exportSpotter', {
                    params: {
                        ...this.filters
                    },
                    responseType: 'arraybuffer'
                }).then(res => {
                    if(res){
                        store.alerts.push({text: 'Exportando contatos 👍', variant:'success'})

                        let blob = new Blob([res.data], { type: 'application/vnd.ms-excel' });

                        var downloadLink = document.createElement("a");

                        url = window.URL.createObjectURL(blob);
                        downloadLink.href = url;
                        downloadLink.download = "contatos_climb_spotter.xlsx";

                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);

                    }else{
                        store.alerts.push({text: 'Erro ao exportar contato 😢', variant:'danger'})
                    }

                }).catch((errors) => {
                    store.alerts.push({text: 'Algo deu errado tente mais tarde 😢', variant:'danger', delay:'3500'})
                })
            },

            formatarCpfCnpj(){
                if(this.contato.cpf_cnpj){
                    this.contato.cpf_cnpj = this.contato.cpf_cnpj.replace(/(\.|\/|\-)/g,"");

                    if(this.contato.cpf_cnpj.length == 14){
                        this.contato.cpf_cnpj = this.contato.cpf_cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,"\$1.\$2.\$3\/\$4\-\$5");
                    }else{
                        this.contato.cpf_cnpj = this.contato.cpf_cnpj.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g,"\$1.\$2.\$3\-\$4");
                    }
                }
            },
            isNumber(evt){
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                    evt.preventDefault();;
                } else {
                    return true;
                }
            }


        },
        watch: {
            page() {
                this.loadContatos()
            }
        },
        computed: {
            optionsCargo() {
                return this.cargos.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsDepartamento() {
                return this.departamentos.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsProfissao() {
                return this.profissoes.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsGrupoContato() {
                return this.grupos_contatos.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsCampanha() {
                return this.campanhas.map(o => ({
                    value: o.id,
                    text: o.nome
                }))
            },
            optionsExibirContatos() {
                return this.exibir_contatos.map(o => ({
                    value: o.value,
                    text: o.text
                }))
            },
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            }
        },
        filters: {
            datetime(date) {
                let arr = date.split(' ')
                let data = arr[0].split('-')
                let horario = arr[1].split(':')
                data.shift()
                horario.pop()
                return data.reverse().join('/') + ' ' + horario.join(':')
            },
        },
        created() {
            this.loadContatos()
            this.loadCargos()
            this.loadDepartamentos()
            this.loadProfissoes()
            this.loadGrupoContatos()
            this.loadCampanhas()
        },
        template: `@yield('interface')`
    })

</script>
@endpush

<style>
.listBounce:hover {
    cursor: pointer;
    background: #cccfff;
}
</style>
