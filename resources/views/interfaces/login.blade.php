<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>StockPro - Login</title>
        <script src="{{ url('assets/vue/vue.js') }}"></script>
        <script src="{{ url('assets/axios/axios.js') }}"></script>
        <link rel="icon" href="{{url('images/logo-symbol-16x16.png')}}" sizes="16x16">
        <link rel="icon" href="{{url('images/logo-symbol-32x32.png')}}" sizes="32x32">
        <link rel="icon" href="{{url('images/logo-symbol-48x48.png')}}" sizes="48x48">
        <link rel="icon" href="{{url('images/logo-symbol-64x64.png')}}" sizes="64x64">
        <link rel="icon" href="{{url('images/logo-symbol-128x128.png')}}" sizes="128x128">
        <style media="screen">

            @import url('https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap');

            * {
                margin: 0;
                padding: 0;
                border: none;
                font-family: 'Open Sans', sans-serif;
            }

            html, body {
                height: 100%;
            }

            body {
                background: #65a2ff;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #login {
                padding: 25px;
                background: #fff;
                box-shadow: 0 0 5px #0065ff;
                border-radius: 3px;
                width: 300px;
            }

            h1 {
                font-size: 1.2em;
                text-align: center;
                font-weight: normal;
                margin: 20px 0 40px 0;
            }

            input {
                border: none;
                box-sizing: border-box;
                width: 100%;
                padding: 10px 15px;
                margin-bottom: 15px;
                background: #f5f5f5;
                font-size: .8em;
            }

            label {
                display: block;
                margin-bottom: 10px;
                font-size: .9em;
            }

            button {
                width: 100%;
                padding: 10px;
                background: #4a90ff;
                color: #fff;
                margin-top: 20px;
                font-size: .8em;
            }

            button:enabled {
                cursor: pointer;
            }

            button:hover:enabled, button:focus:enabled {
                background: #3583ff;
            }

            button:disabled {
                background: #b9b9b9;
            }

            .error {
                background: #F44336;
                color: #fff;
                padding: 10px;
                font-size: .8em;
                border-radius: 3px;
                margin-bottom: 20px;
            }

            .lds-ring {
                display: inline-block;
                position: relative;
                width: 80px;
                height: 80px;
            }
            .lds-ring div {
                box-sizing: border-box;
                display: block;
                position: absolute;
                width: 64px;
                height: 64px;
                margin: 8px;
                border: 8px solid #fff;
                border-radius: 50%;
                animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
                border-color: #fff transparent transparent transparent;
            }
            .lds-ring div:nth-child(1) {
                animation-delay: -0.45s;
            }
            .lds-ring div:nth-child(2) {
                animation-delay: -0.3s;
            }
            .lds-ring div:nth-child(3) {
                animation-delay: -0.15s;
            }
            @keyframes lds-ring {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }

        </style>
    </head>
    <body>
        <div id="app">
            <div v-if="loading" class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div v-if="!loading" id="login">

                <div style="padding: 20px; text-align: center;" class="header">
                  <img src="{{url('images/logo-vertical-247x200.png')}}">
                </div>

                <div v-if="message" class="error">
                    <span v-html="message"></span>
                </div>

                <form @submit.prevent="tryLogin">

                    <label for="email">E-mail</label>

                    <input id="email"
                           v-model="email"
                           type="text"
                           placeholder="exemplo@email.com.br">

                    <label for="senha">Senha</label>

                    <input id="senha"
                           v-model="senha"
                           type="password"
                           placeholder="senha">

                    <button>Entrar</button>

                </form>
            </div>
        </div>
        <script>
            var x = new Vue({
                el: '#app',
                data: {
                    email: null,
                    senha: null,
                    message: null,
                    loading: false
                },
                methods: {
                    tryLogin() {

                        if (this.loading) return;

                        this.message = null
                        this.loading = true

                        axios.post('api/usuario/login', {
                            email: this.email,
                            senha: this.senha
                        }).then(res => {


                            if (res.data.errors) {
                                this.loading = false
                                this.message = res.data.errors.join('<br>')
                            } else {
                                localStorage.apiToken = res.data.token
                                window.location = 'inicio'
                            }

                        }).catch(error => {
                            this.loading = false
                            this.message = 'Ocorreu um erro desconhecido ao realizar o login<br>Tente novamente mais tarde ou entre em contato com o suporte'
                        })

                    }
                }
            })
        </script>
    </body>
</html>
