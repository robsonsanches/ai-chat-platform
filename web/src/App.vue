<script setup>
import { onMounted, ref } from 'vue'
import { Modal } from 'bootstrap'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { APP_NAME, APP_SUBTITLE } from './config/app'
import api from './services/api'

const router = useRouter()

const conversations = ref([])
const messages = ref([])
const loading = ref(false)
const error = ref('')
const selectedConversationId = ref(null)
const conversationPage = ref(1)
const conversationLastPage = ref(1)
const hasMoreConversations = ref(false)
const loadingMoreConversations = ref(false)
const messagePage = ref(1)
const messageLastPage = ref(1)
const hasMoreMessages = ref(false)
const loadingMoreMessages = ref(false)
const loginForm = ref({
  email: '',
  password: '',
})
const promptText = ref('')
const sendingMessage = ref(false)
const loginMessage = ref('')
const loginMessageType = ref('')
const loginErrors = ref([])
const loginLoading = ref(false)
const isAuthenticated = ref(false)
const userProfile = ref({ name: '', email: '' })

const checkAuth = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    isAuthenticated.value = false
    return
  }

  try {
    await api.get('/auth/me')
    isAuthenticated.value = true
  } catch (err) {
    console.error('Token inválido ou expirado:', err)
    localStorage.removeItem('access_token')
    localStorage.removeItem('user')
    isAuthenticated.value = false
    conversations.value = []
    messages.value = []
    selectedConversationId.value = null
  }
}

const loadUserProfile = async () => {
  try {
    const response = await api.get('/auth/me')
    userProfile.value = response.data
  } catch (err) {
    console.error('Erro ao carregar perfil do usuário:', err)
    // Lidar com erro, talvez forçar logout ou exibir mensagem
  }
}

const loadConversations = async (page = 1, append = false) => {
  if (!isAuthenticated.value) {
    conversations.value = []
    conversationPage.value = 1
    conversationLastPage.value = 1
    hasMoreConversations.value = false
    return
  }

  if (page > 1 && (loadingMoreConversations.value || !hasMoreConversations.value)) {
    return
  }

  if (page === 1) {
    loading.value = true
  } else {
    loadingMoreConversations.value = true
  }

  error.value = ''

  try {
    const response = await api.get('/conversations', {
      params: { page },
    })

    const payload = response.data?.data
    const items = Array.isArray(payload?.data)
      ? payload.data
      : Array.isArray(response.data?.data)
        ? response.data.data
        : []

    if (append) {
      conversations.value = [...conversations.value, ...items]
    } else {
      conversations.value = items
    }

    conversationPage.value = payload?.current_page || page
    conversationLastPage.value = payload?.last_page || 1
    hasMoreConversations.value = conversationPage.value < conversationLastPage.value
  } catch (err) {
    error.value = 'Não foi possível carregar os dados da API.'
    console.error(err)
  } finally {
    if (page === 1) {
      loading.value = false
    } else {
      loadingMoreConversations.value = false
    }
  }
}

const handleHistoryScroll = (event) => {
  const element = event.target
  const threshold = 80

  if (loadingMoreConversations.value || !hasMoreConversations.value) {
    return
  }

  const nearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - threshold

  if (nearBottom) {
    loadConversations(conversationPage.value + 1, true)
  }
}

const loadConversationMessages = async (conversationId, page = 1, append = false) => {
  if (!conversationId) {
    return
  }

  if (page > 1 && (loadingMoreMessages.value || !hasMoreMessages.value)) {
    return
  }

  if (page === 1) {
    loading.value = true
    messages.value = []
    messagePage.value = 1
    messageLastPage.value = 1
    hasMoreMessages.value = false
  } else {
    loadingMoreMessages.value = true
  }

  error.value = ''
  selectedConversationId.value = conversationId

  try {
    const response = await api.get(`/conversations/${conversationId}/messages`, {
      params: { page },
    })

    const payload = response.data?.data
    const items = Array.isArray(payload?.data)
      ? payload.data
      : []

    if (append) {
      messages.value = [...messages.value, ...items]
    } else {
      messages.value = items
    }

    messagePage.value = payload?.current_page || page
    messageLastPage.value = payload?.last_page || 1
    hasMoreMessages.value = messagePage.value < messageLastPage.value

    if (!append) {
      requestAnimationFrame(() => {
        scrollMessagesToBottom()
      })
    }
  } catch (err) {
    error.value = 'Não foi possível carregar as mensagens da conversa.'
    messages.value = []
    console.error(err)
  } finally {
    if (page === 1) {
      loading.value = false
    } else {
      loadingMoreMessages.value = false
    }
  }
}

const handleMessagesScroll = (event) => {
  const element = event.target
  const threshold = 80

  if (loadingMoreMessages.value || !hasMoreMessages.value || !selectedConversationId.value) {
    return
  }

  const nearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - threshold

  if (nearBottom) {
    loadConversationMessages(selectedConversationId.value, messagePage.value + 1, true)
  }
}

const scrollMessagesToBottom = () => {
  const container = document.querySelector('.chat-area')

  if (container) {
    container.scrollTop = container.scrollHeight
  }
}

onMounted(async () => {
  await checkAuth()

  if (isAuthenticated.value) {
    loadConversations()
  }
})

const resetConversationState = () => {
  messages.value = []
  selectedConversationId.value = null
  messagePage.value = 1
  messageLastPage.value = 1
  hasMoreMessages.value = false
  loadingMoreMessages.value = false
  conversationPage.value = 1
  conversationLastPage.value = 1
  hasMoreConversations.value = false
  loadingMoreConversations.value = false
  error.value = ''
  promptText.value = ''
  sendingMessage.value = false
}

const openLoginModal = () => {
  if (!isAuthenticated.value) {
    const modalElement = document.getElementById('loginModal')

    if (modalElement) {
      const bootstrapModal = Modal.getOrCreateInstance(modalElement)
      bootstrapModal.show()
    }
  }
}

const startNewChat = async () => {
  if (!isAuthenticated.value) {
    openLoginModal()
    return
  }

  resetConversationState()
  await loadConversations(1, false)
  router.push({ name: 'home' })
}

const handlePromptFocus = () => {
  if (!isAuthenticated.value) {
    openLoginModal()
  }
}

const sendMessage = async () => {
  if (!isAuthenticated.value) {
    openLoginModal()
    return
  }

  const content = promptText.value.trim()

  if (!content) {
    return
  }

  promptText.value = ''
  sendingMessage.value = true
  error.value = ''

  const payload = {
    message: {
      content,
    },
  }

  if (selectedConversationId.value) {
    payload.conversation_id = selectedConversationId.value
  }

  try {
    const userMessage = {
      id: `local-${Date.now()}`,
      role: 'user',
      content,
    }

    messages.value = [...messages.value, userMessage]

    requestAnimationFrame(() => {
      scrollMessagesToBottom()
    })

    setTimeout(() => {
      scrollMessagesToBottom()
    }, 50)

    const response = await api.post('/conversations', payload)

    const conversationId = response.data?.data?.conversation_id || selectedConversationId.value
    const assistantMessage = response.data?.data?.message

    if (conversationId) {
      selectedConversationId.value = conversationId
    }

    if (assistantMessage) {
      messages.value = [...messages.value, assistantMessage]
    }

    if (!selectedConversationId.value) {
      selectedConversationId.value = conversationId
    }

    if (conversationId) {
      await loadConversations(1, false)
      await loadConversationMessages(conversationId, 1, false)

      requestAnimationFrame(() => {
        scrollMessagesToBottom()
      })

      setTimeout(() => {
        scrollMessagesToBottom()
      }, 150)
    }

    promptText.value = ''
  } catch (err) {
    error.value = 'Não foi possível enviar a mensagem.'
    console.error(err)
  } finally {
    sendingMessage.value = false
  }
}

const handleLogout = async () => {
  try {
    const response = await api.post('/auth/logout')

    if (response.data?.status === 'success') {
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      isAuthenticated.value = false
      conversations.value = []
      messages.value = []
      selectedConversationId.value = null
      messagePage.value = 1
      messageLastPage.value = 1
      hasMoreMessages.value = false
      conversationPage.value = 1
      conversationLastPage.value = 1
      hasMoreConversations.value = false
      error.value = ''
      loginForm.value = {
        email: '',
        password: '',
      }
      promptText.value = ''
    } else {
    }
  } catch (err) {
    console.error('Erro ao fazer logout:', err)
  }
}

const login = async () => {
  loginLoading.value = true
  loginMessage.value = ''
  loginMessageType.value = ''
  loginErrors.value = []

  try {
    const response = await api.post('/auth/login', {
      email: loginForm.value.email,
      password: loginForm.value.password,
    })

    if (response.data?.status === 'success' && response.data?.data?.access_token) {
      localStorage.setItem('access_token', response.data.data.access_token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))

      await checkAuth()
      loginMessageType.value = 'success'
      loginMessage.value = response.data.message || 'Login realizado com sucesso!'
      await loadConversations()

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
        loginMessageType.value = 'error'
        loginMessage.value = response.data?.message || 'Erro de validação'
        loginErrors.value = Object.entries(errors).flatMap(([field, messages]) => {
          const list = Array.isArray(messages) ? messages : [messages]
          return list.map((message) => `${field}: ${message}`)
        })
      } else {
        loginMessageType.value = 'error'
        loginMessage.value = response.data?.message || 'Falha ao autenticar.'
        loginErrors.value = []
      }
    }
  } catch (err) {
    const apiError = err?.response?.data

    if (apiError?.errors && Object.keys(apiError.errors).length) {
      loginMessageType.value = 'error'
      loginMessage.value = apiError.message || 'Erro de validação'
      loginErrors.value = Object.entries(apiError.errors).flatMap(([field, messages]) => {
        const list = Array.isArray(messages) ? messages : [messages]
        return list.map((message) => `${message}`)
      })
    } else {
      loginMessageType.value = 'error'
      loginMessage.value = apiError?.message || 'Falha ao autenticar. Verifique e-mail e senha.'
      loginErrors.value = []
    }

    console.error(err)
  } finally {
    loginLoading.value = false
  }
}
</script>

<template>
  <div class="app-container">

      <aside class="sidebar">
          <div class="brand">{{ APP_NAME }}</div>

          <button class="btn text-white new-chat-btn" @click="startNewChat">
              + Novo Chat
          </button>

          <div v-if="isAuthenticated">

            <div class="history-title">Últimos Prompts</div>

            <div class="history-list" @scroll="handleHistoryScroll">
                <button
                    v-for="conversation in conversations"
                    :key="conversation.id"
                    type="button"
                    class="prompt-item w-100 text-start"
                    :class="{ 'active': selectedConversationId === conversation.id }"
                    @click="loadConversationMessages(conversation.id)"
                >
                    {{ conversation.title }}
                </button>

                <div v-if="loadingMoreConversations" class="small text-secondary mt-2">
                    Carregando mais conversas...
                </div>
            </div>

          </div>

          <div class="sidebar-footer">
              <template v-if="isAuthenticated">
                  <button data-bs-toggle="modal" data-bs-target="#profileModal">
                      Perfil
                  </button>

                  <button data-bs-toggle="modal" data-bs-target="#settingsModal">
                      Configurações
                  </button>
              </template>

              <button v-if="!isAuthenticated" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                  Entrar
              </button>

              <button v-if="isAuthenticated" class="btn btn-outline-light btn-sm" @click="handleLogout">
                  Sair
              </button>

              <button v-if="!isAuthenticated" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
                  Criar conta
              </button>
          </div>
      </aside>

      <main class="main-content">

          <div class="topbar">
              <div>
                  <h5 class="mb-0">{{ APP_SUBTITLE }}</h5>
              </div>

              <div class="text-secondary">
                  {{ APP_NAME }}
              </div>
          </div>

          <div class="chat-area" @scroll="handleMessagesScroll">
              <div class="message-wrapper">
                  <div v-if="!isAuthenticated" class="text-center py-4">
                      <div>Faça login para visualizar as mensagens.</div>
                      <div class="text-secondary small mt-2">Entre para acessar o histórico e continuar a conversa.</div>
                  </div>

                  <div v-else-if="loading" class="text-secondary">Carregando mensagens...</div>
                  <div v-else-if="error" class="text-danger">{{ error }}</div>

                  <template v-else>
                      <div v-for="message in messages" :key="message.id" class="message" :class="message.role === 'user' ? 'user' : 'assistant'">
                          <strong>{{ message.role === 'user' ? 'Você' : 'Assistente' }}</strong>
                          {{ message.content }}
                      </div>

                      <div v-if="sendingMessage" class="small text-secondary mt-2">
                          Pensando ...
                      </div>

                      <div v-if="loadingMoreMessages" class="small text-secondary mt-2">
                          Carregando mais mensagens...
                      </div>
                  </template>
              </div>
          </div>

      </main>

  </div>

  <div class="prompt-container">
      <div class="prompt-box">

          <textarea
              v-model="promptText"
              :readonly="!isAuthenticated"
              placeholder="Digite seu prompt aqui..."
              @focus="handlePromptFocus"
              @keydown.enter.prevent="sendMessage"
          ></textarea>

          <div class="prompt-actions justify-content-end">
              <button class="send-btn" :disabled="sendingMessage || !isAuthenticated" @click="sendMessage">
                  Enviar
              </button>
          </div>

      </div>
  </div>

  <!-- Modal Perfil -->
  <div class="modal fade" id="profileModal" tabindex="-1" @shown.bs.modal="loadUserProfile">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Perfil</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                  <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" class="form-control" v-model="userProfile.name">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input type="email" class="form-control" v-model="userProfile.email">
                  </div>

              </div>

              <div class="modal-footer border-0">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-success">Salvar</button>
              </div>

          </div>
      </div>
  </div>

  <!-- Modal Configurações -->
  <div class="modal fade" id="settingsModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Configurações</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                  <div class="mb-3">
                      <label class="form-label">Tema</label>

                      <select class="form-select">
                          <option>Escuro</option>
                          <option>Claro</option>
                      </select>
                  </div>

              </div>

              <div class="modal-footer border-0">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-success">Salvar</button>
              </div>

          </div>
      </div>
  </div>

  <!-- Modal Login -->
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

                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" checked>
                      <label class="form-check-label">Lembrar-me</label>
                  </div>

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

  <!-- Modal Registro -->
  <div class="modal fade" id="registerModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Criar conta</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                  <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" class="form-control" placeholder="Seu nome">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input type="email" class="form-control" placeholder="seu@email.com">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Senha</label>
                      <input type="password" class="form-control" placeholder="••••••••">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">Confirmar senha</label>
                      <input type="password" class="form-control" placeholder="••••••••">
                  </div>

              </div>

              <div class="modal-footer border-0">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-success">Criar conta</button>
              </div>

          </div>
      </div>
  </div>

  <RouterView />
</template>
