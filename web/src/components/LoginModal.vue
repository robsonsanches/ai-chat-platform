<template>
  <div class="modal fade" id="loginModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Entrar</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input v-model="loginForm.email" type="email" class="form-control" placeholder="seu@email.com">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Senha</label>
                      <input v-model="loginForm.password" type="password" class="form-control" placeholder="••••••••">
                  </div>

                  <div v-if="loginMessage" class="small" :class="loginMessageType === 'success' ? 'text-success' : 'text-danger'">
                      <div v-if="loginMessageType === 'success'">{{ loginMessage }}</div>
                      <div v-else>
                          <div class="fw-semibold mb-1">{{ loginMessage }}</div>
                          <ul v-if="loginErrors.length" class="ps-3">
                              <li v-for="(errorItem, index) in loginErrors" :key="index">{{ errorItem }}</li>
                          </ul>
                      </div>
                  </div>

                  <!-- <div class="form-check">
                      <input class="form-check-input" type="checkbox" checked>
                      <label class="form-check-label">Lembrar-me</label>
                  </div> -->

              </div>

              <div class="modal-footer border-0">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-success" :disabled="loginLoading" @click="login">
                      {{ loginLoading ? 'Entrando...' : 'Entrar' }}
                  </button>
              </div>

          </div>
      </div>
  </div>
</template>

import { Modal } from 'bootstrap'
import api from '../services/api'

<script>
import { Modal } from 'bootstrap'
import api from '../services/api'

export default {
  emits: ['authenticated', 'conversations-loaded'],
  data() {
    return {
      loginForm: { email: '', password: '' },
      loginMessage: '',
      loginMessageType: '',
      loginErrors: [],
      loginLoading: false,
    }
  },
  methods: {
    async login() {
  this.loginLoading = true
  this.loginMessage = ''
  this.loginMessageType = ''
  this.loginErrors = []

  try {
    const response = await api.post('/auth/login', {
      email: this.loginForm.email,
      password: this.loginForm.password,
    })

    if (response.data?.status === 'success' && response.data?.data?.access_token) {
      localStorage.setItem('access_token', response.data.data.access_token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))

      this.$emit('authenticated', true)
      this.loginMessageType = 'success'
      this.loginMessage = response.data.message || 'Login realizado com sucesso!'
      this.$emit('conversations-loaded')

      const loginModalEl = document.getElementById('loginModal')
      if (loginModalEl) {
        const loginModal = Modal.getOrCreateInstance(loginModalEl)
        loginModal.hide()

        document.body.classList.remove('modal-open')
        const backdrop = document.querySelector('.modal-backdrop')
        if (backdrop) {
          backdrop.remove()
        }
      }

      console.log('Resposta da autenticação:', response.data)
    } else {
      const errors = response.data?.errors

      if (errors && Object.keys(errors).length) {
        this.loginMessageType = 'error'
        this.loginMessage = response.data?.message || 'Erro de validação'
        this.loginErrors = Object.entries(errors).flatMap(([field, messages]) => {
          const list = Array.isArray(messages) ? messages : [messages]
          return list.map((message) => `${field}: ${message}`)
        })
      } else {
        this.loginMessageType = 'error'
        this.loginMessage = response.data?.message || 'Falha ao autenticar.'
        this.loginErrors = []
      }
    }
  } catch (err) {
    const apiError = err?.response?.data

    if (apiError?.errors && Object.keys(apiError.errors).length) {
      this.loginMessageType = 'error'
      this.loginMessage = apiError.message || 'Erro de validação'
      this.loginErrors = Object.entries(apiError.errors).flatMap(([, messages]) => {
        const list = Array.isArray(messages) ? messages : [messages]
        return list.map((message) => `${message}`)
      })
    } else {
      this.loginMessageType = 'error'
      this.loginMessage = apiError?.message || 'Falha ao autenticar. Verifique e-mail e senha.'
      this.loginErrors = []
    }

    console.error(err)
  } finally {
    this.loginLoading = false
  }
    },
  },
}
</script>

<style scoped>
/* Estilos do modal de login */
</style>