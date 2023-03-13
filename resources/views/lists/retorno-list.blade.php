@section('retorno-list')
    <div>
        <c-modal v-bind:value="value" v-on:input="changeModal" :title="title" >
            <c-list :items="retornosCampanha" :loading="loading" style="height: calc(100vh - 280px)" class="mb-3">
                <template v-slot:items="item">
                    <div class="flex-column w-100" :style="styleEmail(item.data.ind_avaliacao)">

                        <div class="d-flex">
                            <span class="text-elipsis" style="font-size: .9em; color: #222;">@{{item.data.nome}}</b></span>
                            <span class="ml-auto">
                                <i :style="styleIconRetorno(item.data)" :class="contatoRetorno(item.data)" :title="titleIcon(item.data.origem_nome)"></i>
                                <i @click="abrirTemplate(item.data.assunto, item.data.mensagem)" class="ml-2 fas fa-envelope-open-text" title="Visualizar resposta" style="color: #65a2ff; cursor:pointer;"></i>
                            </span>
                        </div>

                        <div class="d-flex">
                            <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f">@{{item.data.empresa}}</span>
                            <span class="ml-auto" style="font-size: .8em; color: #222;">@{{item.data.data | date_list}}</span>
                        </div>
                        <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f">@{{item.data.campanha_nome}} (@{{item.data.sequencia_atual}}/@{{item.data.sequencia_maxima}})</span>
                    </div>
                </template>
            </c-list>
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
        </c-modal>
        <c-modal :title="assunto" v-model="modalTemplate" size="full">
            <b-row>
                <b-col>
                    <div v-html="mensagem"></div>
                </b-col>
            </b-row>

        </c-modal>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('retorno-list', {
      data: function () {
        return {
            retornosCampanha: [],
            origensRetornos: [],
            modalTemplate: false,
            mensagem: false,
            assunto: null,
            limit: 15,
            page: 1,
            totalRows: 0,
            loading: false
        }
      },
      props: [
          'campanha_id',
          'value',
          'title',
      ],
      methods: {
          loadRetornos() {
              axios.get('api/retorno', {
                  params: {
                      limit: this.limit,
                      offset: this.page * this.limit - this.limit,
                      sort_by: 'nome',
                      campanha_idClick: this.$props.campanha_id
                  }
              }).then(res => {

                  this.retornosCampanha = res.data.data

                  this.totalRows = res.data.totalRows

                  this.loading = false

              }).catch((error) => {

                  store.alerts.push({text: `Algo deu errado, tente novamente mais tarde!`, variant:'danger'})

              })
          },
          loadOrigem() {
              axios.get('api/origem').then(res => {

                  this.origensRetornos = res.data.data

              }).catch((error) => {

                  store.alerts.push({text: 'Algo deu errado ao carregar as origens 😢', variant:'danger', delay:'3500'})

              })
          },
          changeModal(value){
              this.$emit('input', value)
          },
          styleEmail(ind_avaliacao) {

              let obj = {
                  'padding-left': '.8em'
              }

              if (ind_avaliacao == 1 || ind_avaliacao == null) obj["border-left"] = "5px solid rgb(255, 255, 255)";
              if (ind_avaliacao == 2) obj["border-left"] = "5px solid rgb(252, 198, 45)";
              if (ind_avaliacao == 3) obj["border-left"] = "5px solid rgb(116, 197, 146)";
              if (ind_avaliacao == 4) obj["border-left"] = "5px solid rgb(254, 96, 96)";

              return obj;

          },
          contatoRetorno(classe) {
              let class_icon = this.origensRetornos.find(o => o.id == classe.origem_id);
              return class_icon.icone_classe
          },
          styleIconRetorno(cor){
              let color_icon = this.origensRetornos.find(o => o.id == cor.origem_id);
              let obj = {'color': color_icon.icone_cor}
              return obj
          },
          abrirTemplate(assunto, mensagem) {
              this.assunto = JSON.parse(JSON.stringify(assunto))
              this.mensagem = JSON.parse(JSON.stringify(mensagem))
              this.modalTemplate = true
          },
          titleIcon(nome) {
              return 'Retorno através do ' + nome;
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
      created(){
         this.loadOrigem()
      },
      watch: {
          page() {
              this.loadRetornos()
          },
          value(abrir){
              if(abrir) {
                  this.loadRetornos()
              }
          }
      },
      template: `@yield('retorno-list')`
    })

</script>
@endpush
