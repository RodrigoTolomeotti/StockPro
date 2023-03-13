@extends('layouts.main')

@section('title', 'Campanhas')

@include('components.c-list')
@include('components.c-card')

@push('header')
    <script src="{{ url('assets/ckeditor5/ckeditor.js') }}"></script>
    <script src="{{ url('assets/ckeditor5-vue/ckeditor.js') }}"></script>
@endpush

@section('interface')
    <div class="d-flex flex-column flex-grow-1">

        <c-card reference="Climb > Campanhas" title="Campanhas">

            <template slot="icons">
                <i class="fas fa-plus"></i>
                <i class="fas fa-filter">
                    {{-- <span v-if="totalFilters" class="card-icons-alert">@{{totalFilters}}</span> --}}
                </i>
            </template>

            <ckeditor :editor="ckeditor.editor" :config="ckeditor.editorConfig"></ckeditor>

        </c-card>

    </div>
@endsection

@push('scripts')
<script>

    class ImageUploadAdapter {

        constructor(loader) {
            this.loader = loader
        }

        // Starts the upload process.
        upload() {
            return this.loader.file
                .then(file => new Promise((resolve, reject) => {
                    this._initRequest()
                    this._initListeners(resolve, reject, file)
                    this._sendRequest(file)
                }))
        }

        abort() {
            if (this.xhr) this.xhr.abort()
        }

        // Initializes the XMLHttpRequest object using the URL passed to the constructor.
        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest()

            xhr.open( 'POST', 'api/template/upload-file', true );
            xhr.setRequestHeader('Authorization', "Bearer " + store.apiToken);
            xhr.responseType = 'json';
        }

        // Initializes XMLHttpRequest listeners.
        _initListeners( resolve, reject, file ) {
            const xhr = this.xhr
            const loader = this.loader
            const genericErrorText = `Couldn't upload file: ${ file.name }.`

            xhr.addEventListener('error', () => reject(genericErrorText))
            xhr.addEventListener('abort', () => reject())
            xhr.addEventListener('load', () => {
                const response = xhr.response

                if (!response || response.error) {
                    return reject( response && response.error ? response.error.message : genericErrorText );
                }

                resolve({
                    default: response.url
                })
            })

            if (xhr.upload) xhr.upload.addEventListener( 'progress', evt => {
                if (evt.lengthComputable) {
                    loader.uploadTotal = evt.total
                    loader.uploaded = evt.loaded
                }
            })
        }

        // Prepares the data and sends the request.
        _sendRequest(file) {
            const data = new FormData();

            data.append('image', file)

            this.xhr.send( data )
        }
    }

    function ImageUploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new ImageUploadAdapter(loader)
        }
    }

    Vue.component('interface', {
        components: {
            ckeditor: CKEditor.component
        },
        data() {
            return {
                ckeditor: {
                    editor: ClassicEditor,
                    editorConfig: {
                        extraPlugins: [ImageUploadPlugin],
                        toolbar: ['bold', 'italic', 'link', 'imageUpload', '|', 'undo', 'redo'],
                        image: {
                            toolbar: ['imageStyle:full', '|', 'imageStyle:alignLeft', 'imageStyle:alignCenter', 'imageStyle:alignRight'],
                            styles: ['full', 'alignCenter', 'alignLeft', 'alignRight']
                        }
                    }
                }
            }
        },
        template: `@yield('interface')`
    })

</script>
@endpush
