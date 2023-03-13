@section('c-multiselect')
    <div class="multiselect">

        <div class="multiselect-container" @click="$refs.search.focus()">
            <div v-for="v, i in value" :key="i" class="multiselect-selected">
                @{{options.find(o => o.value == v).text}}
                <span @click="remove(i)" class="multiselect-remove">x</span>
            </div>
            <input v-model="search" ref="search" @focus="open = true" @blur="focusOut" type="text" class="multiselect-input float-left">
        </div>

        <div v-if="open" class="multiselect-options">
            <div v-for="option, i in optionsComputed" @mousedown="select(option)" :key="i" class="multiselect-option">
                @{{option.text}}
            </div>
            <span class="multiselect-notfound" v-if="optionsComputed.length == 0">Nenhum registro encontrado</span>
        </div>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-multiselect', {
        props: [
            'value',
            'options'
        ],
        data() {
            if (!Array.isArray(this.value)) this.$emit('input', [])
            return {
                search: '',
                open: false
            }
        },
        methods: {
            select(option) {
                let x = this.value
                x.push(option.value)
                this.$emit('input', x)
            },
            remove(index) {
                let x = this.value
                x.splice(index, 1)
                this.$emit('input', x)
            },
            focusOut() {
                this.open = false
                this.search = ''
            }
        },
        computed: {
            optionsComputed() {
                return this.options
                           .filter(o => this.value.indexOf(o.value) == -1)
                           .filter(o => this.search == '' || o.text.toLowerCase().indexOf(this.search.toLowerCase()) >= 0)
            }
        },
        template: `@yield('c-multiselect')`
    })

</script>
@endpush

@push('styles')
<style>

    .multiselect {
        position: relative;
    }

    .multiselect-container {
        overflow: auto;
        display: block;
        cursor: text;
        width: 100%;
        min-height: calc(1.5em + .75rem + 2px);
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }

    .multiselect-input {
        border:none;
        background:none;
        outline: none;
        width: 180px;
        margin: .375rem 0 .375rem .75rem;
    }

    .multiselect-selected {
        float: left;
        position: relative;
        margin: .375rem 0 .375rem .75rem;
        padding: 3px 25px 3px 5px;
        background: #bfd9ff;
        border-radius: 3px;
        border: 1px solid #64a2ff;
        font-size: .7em;
    }

    .multiselect-remove {
        cursor: pointer;
        padding: 11px 10px;
        line-height: 0;
        position: absolute;
        right: 0;
        top: 0;
    }

    .multiselect-options {
        position: absolute;
        width: 100%;
        top: 100%;
        border: 1px solid rgb(122, 156, 211);
        padding: 5px 0;
        max-height: 200px;
        overflow: auto;
        background: #fff;
        z-index: 1;
    }

    .multiselect-option {
        padding: 0 12px;
    }

    .multiselect-option:hover {
        background: rgb(30, 144, 255);
        color: #fff;
    }

    .multiselect-notfound {
        font-size: .8em;
        color: #656565;
        padding: 0 15px;
    }

</style>
@endpush
