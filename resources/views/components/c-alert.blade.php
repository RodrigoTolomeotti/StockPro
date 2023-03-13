@section('c-alert')
    <div>
        <transition-group tag="div" class="alert-group" name="alert">
            <div v-for="alert, i in alerts" v-if="!alert.close" :key="i" class="alert" :class="{['bg-' + alert.variant]: true}">
                <span @click="alert.close = true">@{{ alert.text }}</span>
            </div>
        </transition-group>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-alert', {
        data() {
            return {
                alerts: store.alerts
            }
        },
        watch: {
            alerts(open) {

                open.forEach((o, i) => {

                    if (!o.timeout) {
                        o.timeout = setTimeout(() => {
                            this.alerts.splice(this.alerts.indexOf(o), 1)
                        }, o.delay ? o.delay : 2000)
                    }
                })

            }
        },
        template: `@yield('c-alert')`
    })

</script>
@endpush

@push('styles')
<style>

    .alert-group {
        position: fixed;
        z-index: 999999;
        bottom: 0;
        right: 0;
        padding: 10px;
        display: flex;
        flex-direction: column-reverse;
    }

    .alert {
        /* background: #64a2ff; */
        color: #fff;
        border-radius: 5px;
        padding: 15px;
        margin-top: 5px;
        font-size: .8em;
    }

    .alert-enter-active {
        opacity: 0.8;
        transform: translate(100%);
        transition: .5s;
    }

    .alert-enter-to {
        opacity: 1;
        transform: translate(0);
    }

    .alert-leave-active {
        opacity: 1;
        transform: translate(0);
        transition: .5s;
    }

    .alert-leave-to {
        opacity: 0;
        transform: translate(100%);
    }

</style>
@endpush
