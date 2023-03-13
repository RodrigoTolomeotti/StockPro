@include('components.c-notifications')
@include('components.c-modal')
@include('components.c-alert')
<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>StockPro - @yield('title')</title>

        <link type="text/css" rel="stylesheet" href="{{ url('assets/bootstrap-vue/bootstrap.min.css') }}" />
        <link type="text/css" rel="stylesheet" href="{{ url('assets/bootstrap-vue/bootstrap-vue.min.css') }}" />

        <link rel="stylesheet" href="{{ url('assets/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ url('main.css') }}">

        <script src="{{ url('assets/vue/vue-dev.js') }}"></script>
        {{-- <script src="{{ url('assets/vue/vue.js') }}"></script> --}}
        <script src="{{ url('assets/axios/axios.js') }}"></script>
        <script src="{{ url('assets/bootstrap-vue/bootstrap-vue.min.js') }}"></script>
        <script src="{{ url('assets/vue-clickaway/vue-clickaway.min.js') }}"></script>

        @stack('header')

        @stack('styles')

        <script>

            var store = {
                apiToken: localStorage.apiToken,
                baseUrl: '{{ url() }}',
                apiUrl: '{{ url('api') }}',
                alerts: []
            }

            if (!localStorage.apiToken || localStorage.apiToken == '') {
                window.location = store.baseUrl + '/login'
            }

            axios.defaults.headers.common = {
                'Authorization': "Bearer " + localStorage.apiToken
            }

            axios.interceptors.response.use(function (response) {
                // Do something with response data
                return response;
            }, function (error) {
                if (error.response.status == 401) {
                    window.location = store.baseUrl + '/login'
                }
                return error;
            });

        </script>

    </head>
    <body>
        <div id="loading">
            <div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div id="app">

            <transition name="sidebar">

                <div v-if="showSidebar" class="sidebar">

                    <div class="sidebar-title">
                        STOCKPRO
                    </div>
                    <div class="sidebar-items">

                        <a disabled v-for="tela in telas.filter(o => o.menu_destino == 1)" :href="tela.url">
                            <div class="sidebar-item">
                                <i :class="tela.icone_classe"></i>
                                @{{tela.nome}}
                            </div>
                        </a>

                    </div>

                </div>

            </transition>

            <div class="screen m-0 p-0">
                <div class="screen-top">
                    <div class="screen-title">
                        <i @click="toggleSidebar" class="fas fa-bars"></i>
                        <h1 class="m-0">@yield('title')</h1>
                    </div>
                    <div v-if="currentUser" class="screen-user">

                        <c-notifications class="mr-2"></c-notifications>

                        <div v-if="currentUser.imagem"
                             class="user-icon-image"
                             :style="iconStyle">
                        </div>
                        <div v-else class="user-icon">
                            @{{currentUser.nome[0].toUpperCase()}}
                        </div>

                        <span @click="openUserMenu = !openUserMenu" class="user-name">@{{currentUser.nome.toUpperCase()}}</span>
                        <i @click="openUserMenu = !openUserMenu" class="fas fa-caret-down"></i>
                        <transition name="user-menu">
                            <div v-if="openUserMenu" class="user-menu">
                                <div v-for="tela in telas.filter(o => o.menu_destino == 2)"
                                     @click="redirect(tela.url)"
                                     class="user-menu-item">@{{tela.nome}}</div>
                                <div class="user-menu-separator"></div>
                                <div @click="logout" class="user-menu-item">Logout</div>
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="screen-content">
                    <interface></interface>
                </div>
            </div>

            <c-alert></c-alert>

        </div>

        @stack('scripts')

        <script>

            new Vue({
                el: '#app',
                data: {
                    currentUser: null,
                    openUserMenu: false,
                    showSidebar: typeof localStorage.showSidebar == 'undefined' || localStorage.showSidebar == "true",
                    telas: []
                },
                mixins: [ VueClickaway ],
                methods: {
                    getCurrentUser() {
                        axios.get(store.apiUrl + '/usuario').then(res => {
                            this.currentUser = res.data.data
                            document.getElementById('loading').remove()
                        })
                    },
                    getTelas() {
                        axios.get(store.apiUrl + '/usuario/telas').then(res => {
                            this.telas = res.data.data
                        })
                    },
                    logout() {
                        localStorage.apiToken = null
                        window.location = store.baseUrl + '/login'
                    },
                    redirect(link) {
                        window.location = store.baseUrl + '/' + link
                    },
                    toggleSidebar() {
                        this.showSidebar = !this.showSidebar
                        localStorage.showSidebar = this.showSidebar
                    }
                },
                computed: {
                    iconStyle() {
                        return {
                            'background-image': 'url(\'' + store.baseUrl + '/users/' +  this.currentUser.imagem + '\')'
                        }
                    }
                },
                created() {
                    this.getCurrentUser()
                    this.getTelas()
                }
            })
        </script>

    </body>
</html>
