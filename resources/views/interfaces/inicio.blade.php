@extends('layouts.main')

@section('title', 'Início')

@include('components.c-card')
@include('components.c-list')
@include('lists.envio-list')
@include('lists.retorno-list')
@include('lists.contato-list')

@section('component.interface')

@push('header')
    <script src="{{ url('assets/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ url('assets/apexcharts/vue-apexcharts.js') }}"></script>
@endpush

@section('interface')
    {{-- <div style="text-align: center;color: #616161; margin-top: 50px;">
        <h1 style="font-weight: normal;">
            Olá
        </h1>
        <h2 style="font-weight: normal;margin-top: 20px;">
            Seja bem vindo ao <span style="color:#4a90fe">Climb</span>
        </h2>
    </div> --}}
    <div>
        <b-row>
            <b-col cols="12">
                <c-card title="Pedidos por vendedor" id="graficoCampanhas">
                    <template slot="icons">
                        <i @click="abrirFiltros" class="fas fa-filter">
                            <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span>
                        </i>
                    </template>
                    <div v-if="!campanhas" class="d-flex p-3 justify-content-center">
                        <b-spinner class="mr-2"variant="primary" label="Spinning"></b-spinner>
                        <label >Carregando...</label>
                    </div>
                    <div v-if="flg_campanhas" class="d-flex p-3 justify-content-center m-2">
                        <label>Sem pedidos por vendedor.</label>
                    </div>
                    <div v-else>
                        <apexchart v-if="campanhas" type="bar" :options="graficoCampanhas.chartOptions" :series="graficoCampanhas.series"></apexchart>
                    </div>
                </c-card>
            </b-col>
            <b-col cols="12" md="12" lg="12" xl="4">
                <c-card title="Pedidos hoje">
                    <div v-if="!envios" class="d-flex p-3 justify-content-center m-2">
                        <b-spinner class="mr-2"variant="primary" label="Spinning"></b-spinner>
                        <label >Carregando...</label>
                    </div>
                    <div v-if="flg_envios" class="d-flex p-3 justify-content-center m-2">
                        <label>Sem pedidos hoje.</label>
                    </div>
                    <div v-else>
                        <apexchart v-if="envios" type="donut" height="200" :options="graficoEnviosHoje.chartOptions" :series="graficoEnviosHoje.series"></apexchart>
                    </div>
                </c-card>
            </b-col>
            <b-col cols="12" md="12" lg="12" xl="4">
                <c-card title="Pedidos Efetivados hoje">
                    <div v-if="!visualizados" class="d-flex p-3 justify-content-center m-2">
                        <b-spinner class="mr-2"variant="primary" label="Spinning"></b-spinner>
                        <label >Carregando...</label>
                    </div>
                    <div v-if="flg_visualizados" class="d-flex p-3 justify-content-center m-2">
                        <label>Pedidos efetivados hoje.</label>
                    </div>
                    <div v-else>
                        <apexchart v-if="visualizados" type="donut" height="200" :options="graficoVisualizadosHoje.chartOptions" :series="graficoVisualizadosHoje.series"></apexchart>
                    </div>
                </c-card>
            </b-col>
            <b-col cols="12" md="12" lg="12" xl="4">
                <c-card title="Orçamentos hoje">
                    <div v-if="!retornos" class="d-flex p-3 justify-content-center m-2">
                        <b-spinner class="mr-2"variant="primary" label="Spinning"></b-spinner>
                        <label >Carregando...</label>
                    </div>
                    <div v-if="flg_retornos" class="d-flex p-3 justify-content-center m-2">
                        <label>Sem orçamentos hoje.</label>
                    </div>
                    <div v-else>
                        <apexchart v-if="retornos" type="donut" height="200" :options="graficoRetornosHoje.chartOptions" :series="graficoRetornosHoje.series"></apexchart>
                    </div>
                </c-card>
            </b-col>

        </b-row>
        <c-modal title="Filtro" v-model="modalFiltro" size="md">

            <template v-slot:buttons>
                <button @click="limparFiltros">Limpar</button>
                <button @click="filtrarCampanhas">Filtrar</button>
            </template>

            <b-row>
                <b-col>
                    <b-form-group label-size="sm" label="Status" label-for="filtro-statusCampanha">
                        <b-form-select
                        id="filtro-statusCampanha"
                        v-model="temporaryFilters.statusCampanha"
                        :options="opcoesStatusFiltro"
                        class="mb-3"
                        value-field="item"
                        text-field="name">
                        </b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>

        </c-modal>

        <envio-list v-model="modalEnvios" title="Envios" :campanha_id="filters.campanha_idClick">
        </envio-list>

        <envio-list v-model="modalVisualizacoes" title="Visualizações" :campanha_id="filters.campanha_idClick" data_abertura="true">
        </envio-list>

        <retorno-list v-model="modalRetornos" title="Retornos" :campanha_id="filters.campanha_idClick">
        </retorno-list>

        <contato-list v-model="modalContatos" title="Contatos" :campanha_id="filters.campanha_idClick">
        </contato-list>

        <contato-list v-model="modalContatosBloqueados" title="Bloqueios" :campanha_id="filters.campanha_idClick" bloqueio=true>
        </contato-list>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('interface', {
        components: {
          apexchart: VueApexCharts,
        },
        data() {
            return {
                enviosCampanha: [],
                retornosCampanha: [],
                contatosCampanha: [],
                origens: [],
                limit: 15,
                page: 1,
                totalRows: 0,
                temporaryFilters: {},
                filters: {
                    campanha_idClick: null,
                    data_abertura: null
                },
                modalTemplate: false,
                assunto: null,
                mensagem: null,
                contato: {},
                origem: null,
                campanhas: true,
                envios: true,
                visualizados: true,
                retornos: true,
                flg_campanhas: false,
                flg_envios: false,
                flg_visualizados: false,
                flg_retornos: false,
                modalEnvios: false,
                modalVisualizacoes: false,
                modalRetornos: false,
                modalContatos: false,
                modalContatosBloqueados: false,
                modalFiltro: false,
                loading: false,
                temporaryFilters: {},
                filters: {
                    statusCampanha: 1,
                },
                opcoesStatusFiltro: [
                { item: '1', name: 'Ativa'},
                { item: '2', name: 'Inativa'},
                { item: null,name: 'Ambos'}
              ],
            }
        },
        computed: {
            graficoCampanhas() {
                return null;
            },
            graficoEnviosHoje() {
                return null;
            },
            graficoVisualizadosHoje() {
                return null;
            },
            graficoRetornosHoje() {
                return 0;
            },
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
        },
        methods: {
            loadRelatorio() {
                this.flg_campanhas = true;
                this.flg_retornos = true;
                this.flg_envios = true;
                this.flg_visualizados = true;
                return null;
            },
            abrirFiltros() {
                this.temporaryFilters = JSON.parse(JSON.stringify(this.filters))
                this.modalFiltro = true
            },
            filtrarCampanhas() {
                this.modalFiltro = false
                this.filters = JSON.parse(JSON.stringify(this.temporaryFilters))
                this.loadRelatorio()
            },
            limparFiltros() {
                Object.keys(this.temporaryFilters).forEach(k => {
                    this.temporaryFilters[k] = null
                })
            },
        },
        filters: {
            date_list(string) {

                let arr = string.split(' ')
                let dateArray = arr[0].split('-')
                let hourArray = arr[1].split(':')

                let today = new Date()
                today.setHours(0,0,0,0)

                let date = new Date(dateArray[0], dateArray[1]-1, dateArray[2],0,0,0,0)

                if (today.getTime() == date.getTime()) {
                    return hourArray[0] + ':' + hourArray[1]
                } else {
                    return dateArray[2] + '/' + dateArray[1] + ' ' + hourArray[0] + ':' + hourArray[1]
                }

            }
        },
        created() {
            this.loadRelatorio()
            this.loadOrigem()
        },
        watch: {
            page() {
                this.loadContatos()
                this.loadRetornos()
            },
        },
        template: `@yield('interface')`
    })

</script>
@endpush

@endsection

<style>
.lista {
    padding: 1rem !important;
}
</style>
