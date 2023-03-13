@section('contato-list')
    <div>
        <c-modal v-bind:value="value" v-on:input="changeModal" :title="title" >
            <c-list :items="contatosCampanha" :loading="loading" style="height: calc(100vh - 280px)" class="mb-3">
                <template v-slot:items="item">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex flex-column">
                            <div>
                                <span style="font-size: .9em; color: #222; font-weight: bold;">@{{item.data.nome}}</span>
                                <span v-if="item.data.empresa" style="font-size: .9em; color: #222;">da <b>@{{item.data.empresa}}</b></span>
                            </div>
                            <span style="font-size: .8em; color: #6f6f6f">@{{item.data.email}}</span>
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
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('contato-list', {
      data: function () {
        return {
            contatosCampanha: [],
            limit: 15,
            page: 1,
            totalRows: 0,
            loading: false
        }
      },
      props: [
          'campanha_id',
          'bloqueio',
          'value',
          'title',
      ],
      methods: {
          loadContatos(campanha) {
              axios.get('api/contato/contatos/campanhas', {
                  params: {
                      limit: this.limit,
                      offset: this.page * this.limit - this.limit,
                      sort_by: 'nome',
                      campanha_idClick: this.$props.campanha_id,
                      bloqueio: this.$props.bloqueio
                  }
              }).then(res => {

                  this.contatosCampanha = res.data.data

                  this.totalRows = res.data.totalRows

                  this.loading = false

              }).catch((error) => {

                  store.alerts.push({text: 'Algo deu errado ao carregar os contatos 😢', variant:'danger', delay:'3500'})

              })
          },
          changeModal(value){
              this.$emit('input', value)
          },
      },
      watch: {
          page() {
              this.loadContatos()
          },
          value(abrir){
              if(abrir) {
                  this.loadContatos()
              }
          }
      },
      template: `@yield('contato-list')`
    })

</script>
@endpush
