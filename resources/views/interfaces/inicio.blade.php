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
                campanhas: false,
                envios: false,
                visualizados: false,
                retornos: false,
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
                return {
                    series: [
                    {name: 'Contatos', data: this.campanhas.map(o => o.contatos)},
                    {name: 'Bloqueios', data: this.campanhas.map(o => o.bloqueios)},
                    {name: 'Enviados', data: this.campanhas.map(o => o.envios)},
                    {name: 'Visualizados', data: this.campanhas.map(o => o.abertos)},
                    {name: 'Retornos', data: this.campanhas.map(o => o.respostas)},
                    ],
                    chartOptions: {
                    chart: {
                    type: 'bar',
                    height: 430,
                    events: {
                        click: this.clickCampanha,
                      }
                    },
                    plotOptions: {
                    bar: {
                    horizontal: true,
                    dataLabels: {
                    position: 'top',
                    },
                    }
                    },
                    dataLabels: {
                    enabled: true,
                    offsetX: -6,
                    style: {
                    fontSize: '12px',
                    colors: ['#fff']
                    }
                    },
                    stroke: {
                    show: true,
                    width: 1,
                    colors: ['#fff']
                    },
                    xaxis: {
                    categories: this.campanhas.map(o => o.nome),
                    },
                    }
                }
            },
            graficoEnviosHoje() {
                return {
                    series: this.envios.map(o => o.envios),
                    chartOptions: {
                    labels: this.envios.map(o => o.nome),
                    chart: {
                    type: 'donut',
                    },
                    dataLabels: {
                    enabled: false,
                    },
                    responsive: [{
                    breakpoint: 480,
                    options: {
                    chart: {
                    width: 200
                    },
                    legend: {
                    position: 'bottom'
                    }
                    }
                    }],
                    plotOptions: {
                    pie: {
                    donut: {
                    labels: {
                    show: true,
                    value: {
                    fontSize: '1em',
                    offsetY: 0,
                    },
                    total: {
                    show: true
                    }
                    }
                    }
                    }
                    }
                    }
                }
            },
            graficoVisualizadosHoje() {
                return {
                    series: this.visualizados.map(o => o.visualizados),
                    chartOptions: {
                    labels: this.visualizados.map(o => o.nome),
                    chart: {
                    type: 'donut',
                    },
                    dataLabels: {
                    enabled: false,
                    },
                    responsive: [{
                    breakpoint: 480,
                    options: {
                    chart: {
                    width: 200
                    },
                    legend: {
                    position: 'bottom'
                    }
                    }
                    }],
                    plotOptions: {
                    pie: {
                    donut: {
                    labels: {
                    show: true,
                    value: {
                    fontSize: '1em',
                    offsetY: 0,
                    },
                    total: {
                    show: true
                    }
                    }
                    }
                    }
                    }
                    }
                }
            },
            graficoRetornosHoje() {
                return {
                    series: this.retornos.map(o => o.retornos),
                    chartOptions: {
                    labels: this.retornos.map(o => o.nome),
                    chart: {
                    type: 'donut',
                    },
                    dataLabels: {
                    enabled: false,
                    },
                    responsive: [{
                    breakpoint: 480,
                    options: {
                    chart: {
                    width: 200,
                    },
                    legend: {
                    position: 'bottom'
                    }
                    }
                    }],
                    plotOptions: {
                    pie: {
                    donut: {
                    labels: {
                    show: true,
                    value: {
                    fontSize: '1em',
                    offsetY: 0,
                    },
                    total: {
                    show: true
                    }
                    }
                    }
                    }
                    }
                    }
                }
            },
            totalFilters() {
                return Object.keys(this.filters).filter(k => this.filters[k] && this.filters[k] != '').length
            },
        },
        methods: {
            loadRelatorio() {
                axios.get('api/relatorio', {
                    params: {
                        ...this.filters
                    }
                }).then(res => {

                    this.campanhas = res.data.data.campanhas
                    this.retornos = res.data.data.retornos
                    this.envios = res.data.data.envios
                    this.visualizados = res.data.data.visualizados

                    this.campanhas.length == 0 ? this.flg_campanhas = true : this.flg_campanhas = false
                    this.envios.length == 0 ? this.flg_envios = true : this.flg_envios = false
                    this.visualizados.length == 0 ? this.flg_visualizados = true : this.flg_visualizados = false
                    this.retornos.length == 0 ? this.flg_retornos = true : this.flg_retornos = false

                }).catch((error) => {

                    store.alerts.push({text: 'Algo deu errado ao carregar relatório 😢', variant:'danger', delay:'3500'})

                })
            },
            clickCampanha(event, chartContext, config) {

                this.filters.campanha_idClick = this.campanhas[config.dataPointIndex].id

                if(config.seriesIndex == 0) {
                    this.modalContatos = true
                }else if(config.seriesIndex == 1){
                    this.modalContatosBloqueados = true;
                }else if(config.seriesIndex == 2) {
                    this.modalEnvios = true
                }else if(config.seriesIndex == 3) {
                    this.modalVisualizacoes = true
                }else if(config.seriesIndex == 4) {
                    this.modalRetornos = true
                }
            },
            loadOrigem() {
                axios.get('api/origem').then(res => {

                    this.origens = res.data.data

                }).catch((error) => {

                    store.alerts.push({text: 'Algo deu errado ao carregar as origens 😢', variant:'danger', delay:'3500'})

                })
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
