@section('c-pagination')
    <div class="pagination w-100">

        <div class="d-flex">
            <div v-for="option, i in options" :key="i" class="pagination-item" :class="itemClass(option, i)">

                <span class="pagination-item-sequence" @click="selectOption(i)">
                    <i v-if="option.disabled" class="fas fa-lock" style="font-size: .7em"></i>
                    <i v-else-if="currentIndex > i" class="fas fa-check" style="font-size: .7em"></i>
                    <span v-else>@{{i+1}}</span>
                </span>

                <span @click="selectOption(i)" class="pagination-item-text">@{{option.text}}</span>

            </div>
        </div>

        <div class="d-flex align-items-center">
            <span :class="{'pagination-previous-disabled': currentIndex == 0}" class="pagination-previous-span mr-3" @click="prev" :style="{cursor: currentIndex == 0 ? 'default' : 'pointer'}">Anterior</span>
            <button :disabled="currentIndex == options.filter(o => !o.disabled).length - 1" @click="next" class="pagination-next-button" type="button">Próximo</button>
        </div>

    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-pagination', {
        props: [
            'value',
            'options'
        ],
        data() {

            let currentIndex = 0

            if (this.value) {
                currentIndex = this.options.findIndex(o => o.value == this.value)
                if (currentIndex == -1) currentIndex = 0
            }

            this.$emit('input', this.options[currentIndex].value)

            return {
                currentIndex
            }

        },
        methods: {
            itemClass(option, index) {
                return {
                    'pagination-item-wait': this.currentIndex < index,
                    'pagination-item-gone': this.currentIndex > index,
                    'pagination-item-disabled': option.disabled,
                }
            },
            selectOption(index) {

                return false

                let option = this.options[index]

                if (option.disabled) return false

                this.$emit('input', option.value)
                this.currentIndex = index

            },
            prev() {
                let index = this.currentIndex - 1
                let option = this.options[index]
                if (this.isOptionAvaliable(option)) {
                    this.$emit('input', option.value)
                    this.currentIndex = index
                }
            },
            next() {
                let index = this.currentIndex + 1
                let option = this.options[index]
                if (this.isOptionAvaliable(option)) {
                    this.$emit('input', option.value)
                    this.currentIndex = index
                }
            },
            isOptionAvaliable(option) {
                return option && !option.disabled
            }
        },
        template: `@yield('c-pagination')`
    })

</script>
@endpush

@push('styles')
<style>

    .pagination {
        display: flex;
        justify-content: space-between;
    }

    .pagination-item {
        display: flex;
        align-items: center;
        margin-right: 10px;
    }

    .pagination-item span {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8em;
    }

    .pagination-item::before {
        content: '';
        border-bottom: 2px solid #3a89ff;
        width: 50px;
        display: block;
        /* transition: border-color .2s; */
    }

    .pagination-item-sequence {
        width: 30px;
        height: 30px;
        color: #fff;
        background: #3a89ff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 10px;
        /* transition: background-color .2s; */
    }

    /* .pagination-item:not(:last-child) {
        margin-right: 30px;
    } */

    .pagination-next-button {
        background: #3a89ff;
        color: #fff;
        padding: 5px 10px;
        border-radius: 3px;
        transition: all .2s;
    }


    .pagination-next-button:hover {
        background: #1e78ff;
    }

    .pagination-next-button:disabled {
        background: #b9b9b9;
    }

    .pagination-previous-span {
        color: #3a89ff;
        transition: all .2s;
        font-size: .8em;
    }

    .pagination-previous-span:not(.pagination-previous-disabled):hover {
        color: #004bbd;
    }

    .pagination-previous-disabled {
        color: #888;
    }

    .pagination-item-wait::before {
        border-color: #92bfff;
    }

    .pagination-item-wait .pagination-item-sequence {
        background: #92bfff;
    }

    .pagination-item-wait.pagination-item-disabled::before {
        border-color: #ccc;
    }

    .pagination-item-disabled .pagination-item-sequence {
        background: #ccc;
    }

    .pagination-item-gone::before {
        border-color: #00c521;
        border-color: #222;
    }

    .pagination-item-gone .pagination-item-sequence {
        background: #00c521;
        background: #222;
    }

    /* .pagination-item-text, .pagination-item-sequence {
        cursor: pointer;
    }

    .pagination-item-disabled > .pagination-item-text, .pagination-item-disabled > .pagination-item-sequence {
        cursor: default;
    } */

</style>
@endpush
