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

            #container {
                padding: 25px;
                width: 425px;
            }

            #container h1 {
                font-size: 29px;
                line-height: 32px;
                text-align: center;
                font-weight: normal;
                margin: 20px 0 20px 0;
                color: #fff;
                text-shadow: 0px 0px 1px #000;
            }

            #container p {
                text-align: center;
                color: #fff;
                text-shadow: 0px 0px 1px #000;
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
            <div id="container">
                <div style="padding: 20px; text-align: center;" class="header">
                  <img src="{{url('images/logo-vertical-white-247x200.png')}}">
                </div>
                <h1>Parece que você se perdeu durante sua subida</h1>
                <p> Volte ao seu dashboard e continue subindo.</p>
            </div>
        </div>
    </body>
</html>
