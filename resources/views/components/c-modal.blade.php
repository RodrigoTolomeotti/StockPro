@section('c-modal')
    <div>
        <transition name="modal-full-screen">
            <div v-if="value" class="modal-full-screen-overall">
                <div v-if="value" ref="modal" class="modal-full-screen" :class="modalClass" @keydown.esc="close" tabindex="0">

                    <div class="modal-full-screen-top">

                        <i @click="close" class="fas fa-times"></i>
                        <span class="modal-full-screen-title">@{{title}}</span>

                        {{-- <div class="modal-full-screen-top-buttons">
                            <slot name="buttons"></slot>
                        </div> --}}

                    </div>

                    <div class="flex-grow-1 container-fluid p-5 h-100 overflow-auto">
                        <slot></slot>
                    </div>

                    <div class="modal-full-screen-bottom">
                        <div class="modal-full-screen-bottom-buttons">
                            <slot name="buttons"></slot>
                        </div>
                        <slot name="bottom"></slot>
                    </div>

                </div>
            </div>
        </transition>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-modal', {
        props: [
            'value',
            'title',
            'size'
        ],
        data() {
            return {
            }
        },
        computed: {
            modalClass() {
                return {
                    ['modal-full-screen-' + this.size]: true
                }

            }
        },
        methods: {
            close() {
                this.$emit('input', false)
            }
        },
        watch: {
            value(open) {
                if (open) setTimeout(() => this.$refs.modal.focus(), 10)
            }
        },
        template: `@yield('c-modal')`
    })

</script>
@endpush

@push('styles')
<style>

    .modal-full-screen-overall {
        position: fixed;
        z-index: 999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 2%;
        margin: 0;
        background: rgba(0,0,0,0.5);
    }

    .modal-full-screen {
        display: flex;
        flex-direction: column;
        margin: auto;
        max-height: 100%;
        background: #fff;
        outline: none;
        box-shadow: 0 0 10px #222;
        border-radius: 5px;
    }

    .modal-full-screen-full {
        width: 100%;
        height: 100%;
    }

    .modal-full-screen-lg {
        max-width: 800px;
    }

    .modal-full-screen-md {
        max-width: 500px;
    }

    .modal-full-screen-sm {
        max-width: 300px;
    }

    .modal-full-screen-top {
        padding: 0 20px;
        display: flex;
        z-index: 1;
        align-items: center;
        width: 100%;
        min-height: 70px;
        background: #64a2ff;
        color: #fff;
        border-radius: 5px 5px 0 0;
    }

    .modal-full-screen-top .fa-times {
        margin-right: 10px;
        cursor: pointer;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-full-screen-top .fa-times:hover {
        background: rgba(0, 0, 0, .05);
    }

    .modal-full-screen-top .modal-full-screen-top-buttons {
        display: flex;
        margin-left: auto;
    }

    .modal-full-screen-top button {
        display: flex;
        background: none;
        color: white;
        justify-content: center;
        align-items: center;
        height: 100%;
        padding: 15px;
        cursor: pointer;
    }

    .modal-full-screen-top button:hover {
        background: #4a90ff;
    }

    .modal-full-screen-bottom {
        padding: 0 20px;
        display: flex;
        z-index: 1;
        align-items: center;
        width: 100%;
        border-top: 1px solid #ececec;
    }

    .modal-full-screen-bottom .modal-full-screen-bottom-buttons {
        display: flex;
        margin-left: auto;
        color: #fff;
    }

    .modal-full-screen-bottom button {
        display: flex;
        color: white;
        background: #63a2ff;
        font-size: .8em;
        justify-content: center;
        align-items: center;
        padding: 8px 20px;
        border-radius: 20px;
        margin: 20px 10px 20px 0;
    }

    .modal-full-screen-bottom button:not(:disabled) {
        cursor: pointer;
    }

    .modal-full-screen-bottom button:last-child {
        margin-right: 0;
    }

    .modal-full-screen-bottom button:not(:disabled):hover {
        background: #4a90ff;
    }

    .screen-title .modal-full-screen-title {
        font-size: 16px;
        font-weight: normal;
    }

    .modal-full-screen-enter-active {
        opacity: 0;
        transition: .5s;
    }

    .modal-full-screen-enter-to {
        opacity: 1;
    }

    .modal-full-screen-leave-active {
        opacity: 1;
        transition: .5s;
    }

    .modal-full-screen-leave-to {
        opacity: 0;
    }

    .modal-full-screen-enter-active > .modal-full-screen {
        margin-top: 100%;
        transition: .5s;
    }

    .modal-full-screen-enter-to > .modal-full-screen {
        margin-top: 0;
    }

    .modal-full-screen-leave-active > .modal-full-screen {
        margin-top: 0;
        transition: .5s;
    }

    .modal-full-screen-leave-to > .modal-full-screen {
        margin-top: 100%;
    }

</style>
@endpush
