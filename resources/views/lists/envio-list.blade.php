@section('envio-list')
    <div>
        <c-modal v-bind:value="value" v-on:input="changeModal" :title="title" >
            <c-list :items="enviosCampanha" :loading="loading" style="height: calc(100vh - 280px)" class="mb-3">
                <template v-slot:items="item">
                    <div class="flex-column w-100">
                        <div class="d-flex">
                            <span class="text-elipsis" style="font-size: .9em; color: #222;"><b>@{{item.data.contato}}</b></span>
                            <span class="ml-auto" style="font-size: .9em; color: #222;">
                                <span v-if="item.data.data_abertura !== null" :title="titleIcon('2', item.data.data_abertura)">
                                    <i class="fas fa-eye" style="color: #65a2ff; margin-left: 5px;"></i>
                                </span>
                                <span :title="titleIcon('1', item.data.envio)">
                                    <i class="fas fa-paper-plane" @click="abrirTemplate(item.data.assunto, item.data.mensagem)" style="color: #65a2ff; margin-left: 5px; margin-right: 5px; cursor:pointer;"></i>@{{item.data.envio | date_list}}
                                </span>
                            </span>
                        </div>
                        <div class="d-flex">
                            <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f">@{{item.data.empresa}}</span>
                            <span class="ml-auto" style="font-size: .9em; color: #222; margin-left: 5px;">@{{item.data.nome}}</span>
                            <span class="text-elipsis" style="font-size: .8em; color: #6f6f6f; margin-left: 5px;">(@{{item.data.conteudo}})</span>
                        </div>
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

    Vue.component('envio-list', {
      data: function () {
        return {
            enviosCampanha: [],
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
          'data_abertura',
          'value',
          'title',
      ],
      methods: {
          loadEnvios() {
              axios.get('api/envio', {
                  params: {
                      limit: this.limit,
                      offset: this.page * this.limit - this.limit,
                      sort_by: 'nome',
                      campanha_idClick: this.$props.campanha_id,
                      data_abertura: this.$props.data_abertura
                  }
              }).then(res => {

                  this.enviosCampanha = res.data.data

                  this.totalRows = res.data.totalRows

                  this.loading = false

              }).catch((error) => {

                  store.alerts.push({text: 'Algo deu errado ao carregar os envios 😢', variant:'danger', delay:'3500'})

              })
          },
          changeModal(value){
              this.$emit('input', value)
          },
          abrirTemplate(assunto, mensagem) {
              this.assunto = JSON.parse(JSON.stringify(assunto))
              this.mensagem = JSON.parse(JSON.stringify(mensagem))
              this.modalTemplate = true
          },
          titleIcon(op, date) {
              let arr = date.split(' ')
              let data = arr[0].split('-')
              let horario = arr[1].split(':')
              horario.pop()
              if(op == '1'){
                  return 'Enviado em ' + data.reverse().join('/') + ' às ' + horario.join(':')
              }else{
                  return 'Aberto em ' + data.reverse().join('/') + ' às ' + horario.join(':')
              }
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
      watch: {
          page() {
              this.loadEnvios()
          },
          value(x){
              if(x) {
                  this.loadEnvios()
              }
          }
      },
      template: `@yield('envio-list')`
    })

</script>
@endpush
