@section('c-notifications')
    <div style="position: relative">

        <div class="notifications-icon" @click="toggle" :style="style">
            <i class="fas fa-bell"></i>
            <span v-if="qtdNotificacoesPendentes > 0" class="notification-icon-alert">@{{qtdNotificacoesPendentes}}</span>
        </div>

        <transition name="user-menu">
            <div class="box-notifications" v-if="show">

                <p style="font-size: 0.8em;padding: 20px;width: 200px;margin: 0;" v-if="notifications.length == 0">Nenhuma notificação</p>

                <template v-for="notification in notifications">

                    <div @click="open(notification)" class="box-notification" :class="{'box-notification-new': notification.total > notification.visualizacoes}">
                        <div class="d-flex">
                            <span class="notification-title flex-grow-1">@{{notification.titulo}}</span>
                            <span v-if="notification.total > 1" class="notification-title">(@{{notification.total}})</span>
                        </div>
                        <span class="notification-date">@{{notification.data | formatarData}}</span>
                    </div>

                </template>

            </div>
        </transition>

        <c-modal v-model="modal.show" size="md" :title="modal.title">
            <div style="font-size: .9em" v-for="notificacao, i in modal.notificacoes">
                <div class="clearfix mb-3">
                    <div v-html="notificacao.mensagem"></div>
                    <div class="float-right"><b>@{{notificacao.data_criacao | formatarData}}</b></div>
                </div>
                <hr v-if="i < modal.notificacoes.length-1">
            </div>
        </c-modal>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-notifications', {
        data() {
            return {
                notifications: [],
                show: false,
                showModal: false,
                modal: {
                    show: false,
                    title: null,
                    notificacoes: []
                }
            }
        },
        methods: {
            load() {
                axios.get(store.apiUrl + '/notificacao').then(res => {
                    this.notifications = res.data.data
                }).catch(err => {
                    store.alerts.push({text: 'Ocorreu um erro ao buscar suas notificações', variant: 'danger'})
                })
            },
            toggle() {
                this.show = !this.show
            },
            open(notificacao) {
                axios.get(store.apiUrl + '/notificacao/titulo/' + notificacao.titulo)
                .then(res => {

                    console.log(res.data)

                    notificacao.visualizacoes = res.data.length

                    this.show = false

                    this.modal.title = notificacao.titulo
                    this.modal.notificacoes = res.data.data
                    this.modal.show = true

                }).catch(err => {

                    store.alerts.push({text: 'Ocorreu um erro ao gravar a visaulização da notificação', variant: 'danger'})

                })
            }
        },
        computed: {
            qtdNotificacoesPendentes() {
                return this.notifications.filter(n => n.total > n.visualizacoes).length
            },
            style() {
                return {
                    background: this.qtdNotificacoesPendentes ? '#64a2ff' : '#d2d2d2'
                }
            }
        },
        filters: {
            formatarData(data) {
                if(data.length < 25) {
                    let arr = data.split(' ')
                    let date = arr[0].split('-')
                    let hour = arr[1].split(':')
                    return (date.reverse().join('/')) + ' ' + hour[0] + ':' + hour[1]
                }
            }
        },
        created() {
            this.load()
        },
        template: `@yield('c-notifications')`
    })

</script>
@endpush

@push('styles')
<style>
    .notifications-icon {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1em;
        width: 30px;
        height: 30px;
        border-radius: 5px;
        cursor: pointer;
    }
    .notifications-icon > i {
        color: #fff;
    }
    .box-notifications {
        position: absolute;
        right: 0;
        top: 40px;
        background: #fff;
        box-shadow: 0 0 3px #d8d8d8;
        max-height: 300px;
        overflow: auto;
        z-index: 1;
    }
    .box-notification {
        width: 250px;
        padding: 1em;
        border-bottom: 1px solid #efefef;
        cursor: pointer;
    }
    .notification-title {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
    }
    .notification-title {
        font-size: .8em;
        font-weight: bold
    }
    .notification-date {
        font-size: .7em;
        /* text-align: right; */
        display: block;
    }
    .box-notification:hover {
        background: #efefef;
    }
    .box-notification-new {
        color: #222;
        background: #d2e4ff
    }
    .box-notification-new:hover {
        background: #bed8ff
    }
    .notification-icon-alert {
        position: absolute;
        font-weight: bold;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        top: -5px;
        right: -5px;
        font-size: .6em;
        text-align: center;
        line-height: 15px;
        background: red;
        color: white;
    }
</style>
@endpush
