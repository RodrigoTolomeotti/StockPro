@section('c-daysoftheweek')
    <div class="daysoftheweek">

        <div v-for="day, index in languages.abbreviation[language]" :key="index" @click="selectDay(index+1)" :class="{dayactive: value.indexOf(index+1) >= 0}" :title="languages.fullday[language][index]">
            @{{day}}
        </div>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-daysoftheweek', {
        props: {
            value: Array,
            language: {
                type: String,
                default: 'pt-br'
            },
        },
        data() {
            return {
                languages: {
                    fullday: {
                        'pt-br': ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
                        'en': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    },
                    abbreviation: {
                        'pt-br': ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                        'en': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    }
                }
            }
        },
        methods: {
            selectDay(day) {
                let index = this.value.indexOf(day)
                let newValue = this.value
                if (index == -1) {
                    newValue.push(day)
                } else {
                    newValue.splice(index, 1)
                }
                this.$emit('input', newValue)
            }
        },
        template: `@yield('c-daysoftheweek')`
    })

</script>
@endpush

@push('styles')
<style>

    .daysoftheweek {
        display: flex;
        font-size: .65em;
    }

    .daysoftheweek div {
        margin-right: 5px;
        width: 30px;
        text-align: center;
        padding: 5px 0;
        border: 1px solid #ccc;
        cursor: pointer;
        border-radius: .2rem;
        transition: all .2s;
    }

    .daysoftheweek div.dayactive {
        border-color: #4d95ff;
        background-color: #64a2ff;
        color: #fff;
    }

    .daysoftheweek div:hover {
        background-color: #f7f7f7;
    }

    .daysoftheweek div.dayactive:hover {
        background-color: #5298ff;
    }

</style>
@endpush
