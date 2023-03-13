@section('c-singleselect')
    <div class="singleselect">

        <div class="singleselect-container" @click="$refs.search.focus()">
            <div v-for="v, i in value" :key="i" class="singleselect-selected">
                @{{options.find(o => o.value == v).text}}
                <span @click="remove(i)" class="singleselect-remove">x</span>
            </div>
            <input v-model="search" ref="search" @focus="open = true" @blur="focusOut" type="text" class="singleselect-input float-left">
        </div>

        <div v-if="open" class="singleselect-options">
            <div v-for="option, i in optionsComputed" @mousedown="select(option)" :key="i" class="singleselect-option">
                @{{option.text}}
            </div>
            <span class="singleselect-notfound" v-if="optionsComputed.length == 0">Nenhum registro encontrado</span>
        </div>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-singleselect', {
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
                if(x.length >= 1)  x = [];
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
        template: `@yield('c-singleselect')`
    })

</script>
@endpush

@push('styles')
<style>

    .singleselect {
        position: relative;
    }

    .singleselect-container {
        /* overflow: auto; */
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

    .singleselect-input {
        border:none;
        background:none;
        outline: none;
        width: 180px;
        margin: .375rem 0 .375rem .75rem;
    }

    .singleselect-selected {
        float: left;
        position: relative;
        margin: .375rem 0 .375rem .75rem;
        padding: 3px 25px 3px 5px;
        /* background: #bfd9ff;
        border-radius: 3px;
        border: 1px solid #64a2ff; */
        font-size: .9em;
        width: 95%;
    }

    .singleselect-remove {
        cursor: pointer;
        padding: 11px 10px;
        line-height: 0;
        position: absolute;
        right: 0;
        top: 0;
    }

    .singleselect-options {
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

    .singleselect-option {
        padding: 0 12px;
    }

    .singleselect-option:hover {
        background: rgb(30, 144, 255);
        color: #fff;
    }

    .singleselect-notfound {
        font-size: .8em;
        color: #656565;
        padding: 0 15px;
    }

</style>
@endpush
