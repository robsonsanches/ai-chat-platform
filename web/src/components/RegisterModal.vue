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
                  <div v-if="error" class="alert alert-danger" role="alert">{{ error }}</div>

                  <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" class="form-control" :class="{ 'is-invalid': errors.name }" placeholder="Seu nome" v-model="form.name" :disabled="loading">
                      <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                  </div>

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input type="email" class="form-control" :class="{ 'is-invalid': errors.email }" placeholder="seu@email.com" v-model="form.email" :disabled="loading">
                      <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Senha</label>
                      <input type="password" class="form-control" :class="{ 'is-invalid': errors.password }" placeholder="••••••••" v-model="form.password" :disabled="loading">
                      <div v-if="errors.password" class="invalid-feedback">{{ errors.password[0] }}</div>
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Confirmar senha</label>
                      <input type="password" class="form-control" :class="{ 'is-invalid': errors.password_confirmation }" placeholder="••••••••" v-model="form.password_confirmation" :disabled="loading">
                      <div v-if="errors.password_confirmation" class="invalid-feedback">{{ errors.password_confirmation[0] }}</div>
                  </div>

              </div>

              <div class="modal-footer border-0">
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
        password_confirmation: '',
      },
      loading: false,
      error: '',
      errors: {},
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true
      this.error = ''
      this.errors = {}

      try {
        const response = await api.post('/auth/register', {
          name: this.form.name,
          email: this.form.email,
          password: this.form.password,
        })
        this.$emit('registered', response.data.data)
        this.resetForm()
        const modalElement = document.getElementById('registerModal')
        const modal = bootstrap.Modal.getInstance(modalElement)
        if (modal) {
          modal.hide()
        }
      } catch (err) {
        if (err.response && err.response.data) {
          if (err.response.data.code === 'VALIDATION_ERROR') {
            this.errors = err.response.data.errors
            this.error = err.response.data.message
          } else {
            this.error = err.response.data.message || 'Ocorreu um erro ao tentar registrar.'
          }
        } else {
          this.error = 'Ocorreu um erro inesperado. Por favor, tente novamente.'
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
      this.form.password_confirmation = ''
      this.error = ''
      this.errors = {}
    },
  },
}
</script>

<style scoped>
/* Estilos do modal de registro */
</style>