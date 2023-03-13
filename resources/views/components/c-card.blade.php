@section('c-card')
    <div class="card flex-grow-1">

        <template v-if="reference">

            <div class="card-reference">
                @{{reference.toUpperCase()}}
            </div>

            <span class="card-divisor mb-4"></span>

        </template>

        <div class="card-top">
            <div class="card-title">
                @{{title}}
            </div>
            <div class="card-icons">
                <slot name="icons"></slot>
            </div>
        </div>

        <slot></slot>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-card', {
        props: [
            'title',
            'reference'
        ],
        data() {
            return {
            }
        },
        template: `@yield('c-card')`
    })

</script>
@endpush

@push('styles')
<style>

    .card {
        display: flex;
        flex-direction: column;
        background: #fff;
        box-shadow: 0 0 3px #d8d8d8;
        border-radius: 5px;
        margin-bottom: 15px;
        padding: 25px;
        border: none;
    }
    .card-reference {
        font-size: .8em;
        color: #6f6f6f;
        padding: 0 20px 20px 20px;
    }
    .card-divisor {
        width: 100%;
        border-bottom: 1px solid #e9ebee;
    }
    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        margin-bottom: 20px;
    }
    .card-title {
        margin: 0;
        font-size: 1em;
        color: #222;
    }
    .card-icons {
        color: #737373;
        font-size: .8em;
    }
    .card-icons > i {
        margin-left: 15px;
        cursor: pointer;
        position: relative;
    }

    .card-icons > i:hover {
        color: #565656;
    }

    .card-icons-alert {
        position: absolute;
        font-weight: bold;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        top: -10px;
        right: -10px;
        font-size: .8em;
        text-align: center;
        line-height: 15px;
        background: red;
        color: white;
    }

</style>
@endpush
