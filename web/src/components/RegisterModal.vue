<template>
  <!-- Modal Registro -->
  <div class="modal fade" id="registerModal" tabindex="-1" @hidden.bs.modal="resetForm">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Criar conta</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" :disabled="loading"></button>
              </div>

              <div class="modal-body">
                  <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" class="form-control" placeholder="Seu nome" v-model="form.name" :disabled="loading">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input type="email" class="form-control" placeholder="seu@email.com" v-model="form.email" :disabled="loading">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Senha</label>
                      <input type="password" class="form-control" placeholder="••••••••" v-model="form.password" :disabled="loading">
                  </div>

              </div>

              <div class="modal-footer border-0">
                  <div v-if="message" class="alert w-100 mb-3" :class="messageType === 'success' ? 'alert-success' : 'alert-danger'" role="alert">
                      <div>{{ message }}</div>
                      <ul v-if="messageErrors.length" class="mb-0 ps-3">
                          <li v-for="(errorItem, index) in messageErrors" :key="index">{{ errorItem }}</li>
                      </ul>
                  </div>
                  <button class="btn btn-secondary" data-bs-dismiss="modal" :disabled="loading">Cancelar</button>
                  <button class="btn btn-success" @click="handleRegister" :disabled="loading">
                      <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                      Criar conta
                  </button>
              </div>

          </div>
      </div>
  </div>
</template>

<script>
import api from '../services/api'
import * as bootstrap from 'bootstrap'

export default {
  emits: ['registered'],
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
      },
      loading: false,
      message: '',
      messageType: '',
      messageErrors: [],
      errors: {},
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true
      this.message = ''
      this.messageType = ''
      this.messageErrors = []
      this.errors = {}

      try {
        const response = await api.post('/auth/register', {
          name: this.form.name,
          email: this.form.email,
          password: this.form.password,
        })
        this.messageType = 'success'
        this.message = response.data.message || 'Cadastro realizado com sucesso!'
        this.$emit('registered', response.data.data)
        this.resetForm()
        const modalElement = document.getElementById('registerModal')
        if (modalElement) {
          const modal = bootstrap.Modal.getOrCreateInstance(modalElement)
          modal.hide()

          document.body.classList.remove('modal-open')
          const backdrop = document.querySelector('.modal-backdrop')
          if (backdrop) {
            backdrop.remove()
          }
        }
      } catch (err) {
        if (err.response && err.response.data) {
          if (err.response.data.code === 'VALIDATION_ERROR') {
            this.errors = err.response.data.errors
            this.messageType = 'error'
            this.message = err.response.data.message || 'Erro de validação'
            this.messageErrors = Object.values(this.errors).flatMap((messages) => {
              const list = Array.isArray(messages) ? messages : [messages]
              return list.map((message) => `${message}`)
            })
          } else {
            this.messageType = 'error'
            this.message = err.response.data.message || 'Ocorreu um erro ao tentar registrar.'
          }
        } else {
          this.messageType = 'error'
          this.message = 'Ocorreu um erro inesperado. Por favor, tente novamente.'
        }
        console.error(err)
      } finally {
        this.loading = false
      }
    },
    resetForm() {
      this.form.name = ''
      this.form.email = ''
      this.form.password = ''
      this.message = ''
      this.messageType = ''
      this.messageErrors = []
      this.errors = {}
    },
  },
}
</script>

<style scoped>
.alert-success {
  background-color: #00c853;
  border-color: #00a844;
  color: #ffffff;
}

.alert-danger {
  background-color: #ff1744;
  border-color: #d5002f;
  color: #ffffff;
}

/* Estilos do modal de registro */
</style>