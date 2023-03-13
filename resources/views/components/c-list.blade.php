@section('c-list')
    <div ref="list" class="list pt-0 pr-3 pb-3 pl-3 flex-grow-1" @scroll="handleScroll">
        <div class="list-item d-flex  p-3" v-for="item, index in items" :key="index" :class="itemClass">
            <slot name="items" :data="item, index"></slot>
        </div>
    </div>
@endsection

@push('scripts')
<script>

    Vue.component('c-list', {
        props: [
            'value',
            'items',
            'loading',
            'itemClass'
        ],
        data() {
            return {
            }
        },
        methods: {
            handleScroll(e) {
                if (!this.loading && e.srcElement.scrollHeight - e.srcElement.scrollTop == e.srcElement.offsetHeight) {
                    this.$emit('scrollBottom')
                }
            },
        },
        template: `@yield('c-list')`
    })

</script>
@endpush

@push('styles')
<style>

.list {
    height: 210px;
    overflow-y: auto;
    flex-basis: auto;
}

.list-item {
    border-top: 1px solid #e9ebee;
}

.list-icons {
    color: #9f9f9f;
    font-size: .8em;
}

.list-icons > i {
    margin-left: 15px;
    cursor: pointer;
}

.list-icons > i:hover {
    color: #737373;
}


</style>
@endpush
