@extends('layouts.main')

@section('title', 'Configurações')

@include('components.c-card')

@section('interface')
    <div>

        <b-row>

            <b-col>

                <c-card
                    reference="Climb > Configurações"
                    class="mb-2"
                    >

                    <div class="tabs mb-2">
                        <div v-for="tab in tabs"
                             class="tab"
                             @click="activeTab = tab.value"
                             :class="{'active-tab': tab.active}">
                            @{{tab.text}}
                        </div>
                    </div>

                    <transition name="tab-content">

                        <div v-if="tabs[0].active" class="p-3">

                            <div class="user-image mt-2 mb-3"
                                 :class="{'user-image-default': !imagem}"
                                 :style="imageStyle">
                                <span v-if="!imagem" class="user-image-letter">@{{usuario.nome ? usuario.nome[0].toUpperCase() : '?'}}</span>
                                <span @click="$refs.imagem.click()" class="user-image-edit"><i class="fas fa-pen"></i></span>
                                <input ref="imagem" type="file" accept="image/png,image/jpeg" @change="uploadImagem($event)">
                            </div>

                            <b-row>
                                <b-col>

                                    <b-form-group label-size="sm" label="Nome" label-for="nome">
                                        <b-form-input id="nome" v-model="usuario.nome" type="text"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Senha" label-for="input1">
                                        <b-form-input id="input1" name="input1" autocomplete="false" placeholder="*******" v-model="usuario.senha" type="password"></b-form-input>
                                    </b-form-group>

                                </b-col>
                                <b-col>

                                    <b-form-group label-size="sm" label="E-mail" label-for="email">
                                        <b-form-input id="email" disabled v-model="usuario.email" type="email"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Confirmar senha" label-for="input2">
                                        <b-form-input id="input2" name="input2" autocomplete="false" placeholder="*******" v-model="usuario.confirmar_senha" type="password"></b-form-input>
                                    </b-form-group>

                                </b-col>
                            </b-row>

                            <b-btn @click="salvarUsuario" class="float-right" variant="primary">Salvar</b-btn>

                        </div>

                    </transition>
                    <transition name="tab-content">
                        <div v-if="tabs[1].active" class="p-3">

                            <b-row>
                                <b-col>

                                    <b-form-group label-size="sm" label="Conta" label-for="conta_email">
                                        <b-form-input id="conta_email" v-model="conta.conta_usuario" type="text"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Senha" label-for="input3">
                                        <b-form-input id="input3" name="input3" autocomplete="false" placeholder="*******" v-model="conta.conta_senha" type="password"></b-form-input>
                                    </b-form-group>

                                </b-col>
                                <b-col>

                                    <b-form-group label-size="sm" label="E-mail" label-for="email">
                                        <b-form-input id="email" v-model="conta.conta_email" type="email"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Confirmar senha" label-for="input4">
                                        <b-form-input id="input4" name="input4" autocomplete="false" placeholder="*******" v-model="conta.conta_confirmar_senha" type="password"></b-form-input>
                                    </b-form-group>

                                </b-col>
                            </b-row>
                            <b-row>
                                <b-col>

                                    <b-form-group label-size="sm" label="Assinatura" label-for="assinatura">
                                        <b-form-textarea id="assinatura" v-model="conta.assinatura" rows="5" max-rows="5"></b-form-textarea>
                                    </b-form-group>

                                </b-col>
                            </b-row>

                            <b-btn @click="salvarConta" class="float-right" variant="primary">Salvar</b-btn>

                        </div>

                    </transition>
                    <transition name="tab-content">
                        <div v-if="tabs[2].active" class="p-3">

                            <b-row>
                                <b-col>

                                    <b-form-group label-size="sm" label="Servidor (Host)" label-for="smtp_host">
                                        <b-form-input id="smtp_host" v-model="smtp.smtp_host" type="text"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Utilizar protocolo de segurança" label-for="smtp_security">
                                        <b-form-select
                                            v-model="smtp.smtp_security"
                                            :options="opcoesSmtpImap"
                                            value-field="item"
                                            text-field="nome">
                                            <template v-slot:first>
                                                <option :value="null"></option>
                                            </template>
                                        </b-form-select>
                                    </b-form-group>

                                </b-col>
                                <b-col>

                                    <b-form-group label-size="sm" label="Porta (Port)" label-for="smtp_port">
                                        <b-form-input id="smtp_port" v-model="smtp.smtp_port" type="text"></b-form-input>
                                    </b-form-group>

                                </b-col>
                            </b-row>

                            <b-btn v-if="!SMTPverificado" :disabled="true" @click="salvarSMTP" class="float-right" variant="primary">Salvar</b-btn>
                            <b-btn v-else @click="salvarSMTP" class="float-right" variant="primary">Salvar</b-btn>

                            <b-btn v-if="!loading" @click="testarSMTP" class="float-right mr-3" variant="primary">Verificar Conexão</b-btn>
                            <b-btn
                            v-else
                            @click="testarSMTP"
                            class="float-right mr-3"
                            :disabled="loading"
                            variant="primary">
                              <b-spinner class="mr-2" small></b-spinner>Realizando Conexão
                            </b-btn>
                        </div>

                    </transition>
                    <transition name="tab-content">
                        <div v-if="tabs[3].active" class="p-3">

                            <b-row>

                                <b-col>

                                    <b-form-group label-size="sm" label="Servidor (Host)" label-for="imap_host">
                                        <b-form-input id="imap_host" v-model="imap.imap_host" type="text"></b-form-input>
                                    </b-form-group>

                                    <b-form-group label-size="sm" label="Utilizar protocolo de segurança" label-for="imap_security">
                                        <b-form-select
                                            v-model="imap.imap_security"
                                            :options="opcoesSmtpImap"
                                            value-field="item"
                                            text-field="nome">
                                            <template v-slot:first>
                                                <option :value="null"></option>
                                            </template>
                                        </b-form-select>
                                    </b-form-group>

                                </b-col>
                                <b-col>

                                    <b-form-group label-size="sm" label="Porta (Port)" label-for="imap_port">
                                        <b-form-input id="imap_port" v-model="imap.imap_port" type="text"></b-form-input>
                                    </b-form-group>

                                </b-col>
                            </b-row>
                            <b-btn v-if="!IMAPverificado" :disabled="true" @click="salvarIMAP" class="float-right" variant="primary">Salvar</b-btn>
                            <b-btn v-else @click="salvarIMAP" class="float-right" variant="primary">Salvar</b-btn>

                            <b-btn v-if="!loading" @click="testarIMAP" class="float-right mr-3" variant="primary">Verificar Conexão</b-btn>
                            <b-btn
                            v-else
                            @click="testarIMAP"
                            class="float-right mr-3"
                            :disabled="loading"
                            variant="primary">
                              <b-spinner class="mr-2" small></b-spinner>Realizando Conexão
                            </b-btn>

                        </div>

                    </transition>

                </c-card>

            </b-col>

        </b-row>

        <c-modal v-model="modalSenha" title="Confirmar senha" size="md">

            <template v-slot:buttons>
                <button @click="salvarUsuario">Salvar</button>
            </template>

            <p class="mb-3">Para mudar a senha é necessário confirmar a senha atual</p>

            <b-form-group label-size="sm" label="Senha atual" label-for="senha_atual">
                <b-form-input id="senha_atual" v-model="usuario.senha_atual" type="text"></b-form-input>
            </b-form-group>

        </c-modal>

    </div>
@endsection

@push('scripts')
<script>

    function ImageUploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new ImageUploadAdapter(loader)
        }
    }

    Vue.component('interface', {
        data() {
            return {
                modalSenha: false,
                imagem: null,
                user: {},
                activeTab: 1,
                usuario: {
                    nome: null,
                    email: null,
                    senha: "",
                    confirmar_senha: "",
                    senha_atual: ""
                },
                conta: {
                    conta_usuario: null,
                    conta_email: null,
                    conta_senha: "",
                    conta_confirmar_senha: "",
                    assinatura: null
                },
                smtp: {
                    smtp_host: null,
                    smtp_port: null,
                    smtp_security: null
                },
                imap: {
                    imap_host: null,
                    imap_port: null,
                    imap_security: null
                },
                opcoesSmtpImap: [
                    {item: 'ssl', nome: 'ssl'},
                    {item: 'tls', nome: 'tls'},
                ],
                loading: false,
                SMTPverificado: false,
                IMAPverificado: false,
            }
        },
        methods: {
            getUserInfo() {
                axios.get(store.apiUrl + '/usuario').then(res => {

                    let user = res.data.data

                    this.user = user

                    this.imagem = user.imagem
                    this.usuario.nome = user.nome
                    this.usuario.email = user.email
                    this.usuario.senha = ""
                    this.usuario.confirmar_senha = ""

                    this.conta.conta_usuario = user.conta_usuario
                    this.conta.conta_email = user.conta_email
                    this.conta.conta_senha = ""
                    this.conta.conta_confirmar_senha = ""
                    this.conta.assinatura = user.assinatura

                    this.smtp.smtp_host = user.smtp_host
                    this.smtp.smtp_port = user.smtp_port
                    this.smtp.smtp_security = user.smtp_security

                    this.imap.imap_host = user.imap_host
                    this.imap.imap_port = user.imap_port
                    this.imap.imap_security = user.imap_security

                    if(user.smtp_host != null && user.smtp_port!= null) {
                        this.SMTPverificado = false;
                    }
                    if(user.imap_host != null && user.imap_port!= null) {
                        this.IMAPverificado = false;
                    }

                }).catch(error => {
                    if (error.response.status == 401) {
                        window.location = store.baseUrl + '/login'
                    }
                })
            },
            salvarUsuario() {

                let usuario = JSON.parse(JSON.stringify(this.usuario))

                if (!usuario.senha && !usuario.confirmar_senha) {

                    delete usuario.senha
                    delete usuario.confirmar_senha
                    delete usuario.senha_atual

                } else {

                    if (!usuario.senha_atual) {

                        if (this.modalSenha) {

                            store.alerts.push({text: 'Informe a senha atual', variant: 'warning'})

                        } else {

                            this.modalSenha = true

                        }

                        return;

                    }

                    this.usuario.senha_atual = null
                    this.modalSenha = false

                }

                axios.put(store.apiUrl + '/usuario/usuario', usuario).then(res => {
                    if (res.data.errors) {
                        this.throwErrors(res.data.errors);
                        return;
                    }
                    this.usuario.senha = ""
                    this.usuario.confirmar_senha = ""
                    this.user = this.usuario
                    store.alerts.push({text:'Usuário salvo com sucesso', variant:'success'})
                })

            },
            salvarConta() {

                let conta = JSON.parse(JSON.stringify(this.conta))

                if (!conta.conta_senha && !conta.conta_confirmar_senha) {

                    delete conta.conta_senha
                    delete conta.conta_confirmar_senha

                }

                axios.put(store.apiUrl + '/usuario/conta', conta).then(res => {

                    if (res.data.errors) {
                        this.throwErrors(res.data.errors);
                        return;
                    }

                    this.conta.conta_senha = ""
                    this.conta.conta_confirmar_senha = ""

                    this.user = {
                        ...this.user,
                        ...this.conta
                    }

                    store.alerts.push({text:'Conta salva com sucesso', variant:'success'})
                })
            },
            salvarSMTP() {
                axios.put(store.apiUrl + '/usuario/smtp', this.smtp).then(res => {
                    if (res.data.errors) {
                        this.throwErrors(res.data.errors);
                        this.SMTPverificado = false;
                        return;
                    }
                    this.user = {
                        ...this.user,
                        ...this.smtp
                    }
                    store.alerts.push({text:'SMTP salvo com sucesso 👍', variant:'success', delay: 3500})

                })
            },
            testarSMTP() {
              this.loading = true;

              axios.get(store.apiUrl + '/usuario/smtp', {
                  params: {
                      ...this.smtp
                  }
              }).then(res => {

                  if (res.data.errors) {
                      store.alerts.push({text: res.data.errors, variant:'danger'})
                      this.loading = false;
                      return;
                  }

                  if(res.data.errorsSMTP){
                      store.alerts.push({text: res.data.errorsSMTP, variant:'danger', delay: 3500})
                      // store.alerts.push({text:'SMTP salvo com sucesso', variant:'success'})
                      this.loading = false;
                      return;
                  }

                  this.loading = false;
                  this.SMTPverificado = true;

                  store.alerts.push({text:'Não equeça de clicar em Salvar 😉', variant:'info', delay: 5000})
                  store.alerts.push({text:'Conexão realizada com sucesso 👍', variant:'success', delay: 5000})
              })
            },
            salvarIMAP() {
                axios.put(store.apiUrl + '/usuario/imap', this.imap).then(res => {
                    if (res.data.errors) {
                        this.throwErrors(res.data.errors);
                        return;
                    }

                    this.user = {
                        ...this.user,
                        ...this.imap
                    }

                    store.alerts.push({text:'IMAP salvo com sucesso', variant:'success'})
                })
            },
            testarIMAP() {
              this.loading = true;
              axios.get(store.apiUrl + '/usuario/imap', {
                  params: {
                      ...this.imap
                  }
              }).then(res => {

                  if (res.data.errors) {
                      store.alerts.push({text: res.data.errors, variant:'danger', delay: 4500})
                      this.loading = false;
                      this.IMAPverificado = false;
                      return;
                  }

                  store.alerts.push({text:'Conexão realizada com sucesso 👍', variant:'success'})
                  this.IMAPverificado = true;
                  this.loading = false;

              }).catch((error) => {
                  
                  this.loading = false;
                  this.IMAPverificado = false;
                  store.alerts.push({text: 'Erro ao verificar conexão IMAP 😢', variant:'danger', delay:'3500'})

              })
            },
            uploadImagem(event) {

                var formData = new FormData()

                formData.append("imagem", event.target.files[0])

                axios.post(store.apiUrl + '/usuario/imagem', formData, {
                    headers: {
                      'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    if (res.data.errors) {
                        this.throwErrors(res.data.errors);
                        return;
                    }
                    this.$refs.imagem.value = ''
                    this.imagem = res.data.imagem + '?' + Math.random()
                    store.alerts.push({text:'Imagem alterada com sucesso', variant:'success'})
                })

            },
            throwErrors(errors) {
                Object.keys(errors).reverse().forEach(k => {
                    errors[k].reverse().forEach(err => {
                        store.alerts.push({text: err, variant: 'danger'})
                    })
                }, 5000)
            }
        },
        computed: {
            imageStyle() {
                return {
                    'background-image': this.imagem ? 'url(\'' + store.baseUrl + '/users/' + this.imagem + '\')' : 'none'
                }
            },
            isUsuarioChanged() {
                if(this.user.nome != this.usuario.nome || this.usuario.senha != "" || this.usuario.confirmar_senha != "") return true
            },
            isContatoChanged() {
                if(this.conta.conta_usuario != this.user.conta_usuario ||
                    this.conta.conta_email != this.user.conta_email ||
                    this.conta.conta_senha != "" ||
                    this.conta.conta_confirmar_senha != "" ||
                    this.conta.assinatura != this.user.assinatura) return true
            },
            isSMTPChanged() {
                if(this.smtp.smtp_host != this.user.smtp_host ||
                    this.smtp.smtp_port != this.user.smtp_port ||
                    this.smtp.smtp_security != this.user.smtp_security) {
                        this.SMTPverificado = false; return true
                    }
            },
            isIMAPChanged() {
                if(this.imap.imap_host != this.user.imap_host ||
                    this.imap.imap_port != this.user.imap_port ||
                    this.imap.imap_security != this.user.imap_security) {
                        this.IMAPverificado = false; return true}
            },
            tabs() {
                return [
                    {value: 1, text: 'Usuário' + (this.isUsuarioChanged ? ' •' : ''), active: this.activeTab == 1},
                    {value: 2, text: 'Conta de e-mail'  + (this.isContatoChanged ? ' •' : ''), active: this.activeTab == 2},
                    {value: 3, text: 'SMTP'  + (this.isSMTPChanged ? ' •' : ''), active: this.activeTab == 3},
                    {value: 4, text: 'IMAP'  + (this.isIMAPChanged ? ' •' : ''), active: this.activeTab == 4},
                ]
            }
        },
        mounted() {
            this.getUserInfo()
        },
        template: `@yield('interface')`
    })

</script>
@endpush

@push('styles')
<style>

.user-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    box-shadow: 0 10px 15px rgba(0,0,0,0.3);
    position: relative;
    background-size: cover;
}

.user-image-default {
    background: #65a2ff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-image-default .user-image-letter {
    color: #fff;
    font-size: 3em;
}

.user-image .user-image-edit {
    width: 30px;
    height: 30px;
    background: #353535;
    color: #fff;
    position: absolute;
    top: 0;
    right: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .6em;
    cursor: pointer;
    transition: .2s;
}

.user-image .user-image-edit:hover {
    background: #222;
}

.user-image input {
    display: none;
}

.tabs {
    display: flex;
}

.tab {
    padding: .7em 1em;
    color: #ccc;
    cursor: pointer;
    transition: .2s;
    border-bottom: 3px solid #fff;
}

.tab.active-tab {
    color: #65a2ff;
    border-bottom: 3px solid #65a2ff;
}

.tab-content-enter-active {
    transform: translateX(30px);
    opacity: 0;
    transition: .5s;
}

.tab-content-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.tab-content-leave-active {
    display: none;
}

</style>
@endpush
