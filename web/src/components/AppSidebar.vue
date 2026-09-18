<script>
import { APP_NAME } from '../config/app'
import api from '../services/api'
import ConversationList from './ConversationList.vue'

export default {
  components: { ConversationList },
  props: {
    isAuthenticated: { type: Boolean, default: false },
  },
  emits: ['start-new-chat', 'logout', 'conversation-selected'],
  data() {
    return {
      APP_NAME,
      conversations: [],
      loadingConversations: false,
      loadingMoreConversations: false,
      hasMoreConversations: false,
      selectedConversationId: null,
      conversationPage: 1,
      conversationLastPage: 1,
      error: '',
    }
  },
  watch: {
    isAuthenticated(authenticated) {
      if (authenticated) this.loadConversations()
      else this.clearConversations()
    },
  },
  mounted() {
    if (this.isAuthenticated) this.loadConversations()
  },
  methods: {
    clearConversations() {
      this.conversations = []
      this.selectedConversationId = null
      this.conversationPage = 1
      this.conversationLastPage = 1
      this.hasMoreConversations = false
      this.loadingMoreConversations = false
      this.loadingConversations = false
    },
    async loadConversations(page = 1, append = false) {
      if (!this.isAuthenticated) {
        this.clearConversations()
        return
      }
      if (page > 1 && (this.loadingMoreConversations || !this.hasMoreConversations)) return
      if (page > 1) this.loadingMoreConversations = true
      else this.loadingConversations = true
      this.error = ''

      try {
        const response = await api.get('/conversations', { params: { page } })
        const payload = response.data?.data
        const items = Array.isArray(payload?.data)
          ? payload.data
          : Array.isArray(response.data?.data) ? response.data.data : []
        this.conversations = append ? [...this.conversations, ...items] : items
        this.conversationPage = payload?.current_page || page
        this.conversationLastPage = payload?.last_page || 1
        this.hasMoreConversations = this.conversationPage < this.conversationLastPage
      } catch (err) {
        this.error = 'Não foi possível carregar os dados da API.'
        console.error(err)
      } finally {
        if (page > 1) this.loadingMoreConversations = false
        else this.loadingConversations = false
      }
    },
    startNewChat() {
      this.selectedConversationId = null
      this.$emit('start-new-chat')
    },
    handleLogout() { this.$emit('logout') },
    handleLoadMoreConversations() { this.loadConversations(this.conversationPage + 1, true) },
    handleSelectConversation(conversationId) {
      this.selectedConversationId = conversationId
      this.$emit('conversation-selected', conversationId)
    },
  },
}
</script>

<template>
  <aside class="sidebar">
    <div class="brand">{{ APP_NAME }}</div>
    <button class="btn text-white new-chat-btn" @click="startNewChat">+ Novo Chat</button>

    <div v-if="isAuthenticated">
      <div class="history-title">Últimos Prompts</div>
      <div v-if="loadingConversations" class="small text-secondary">Carregando conversas...</div>
      <div v-if="error" class="small text-danger">{{ error }}</div>
      <ConversationList
        :conversations="conversations"
        :loading-more-conversations="loadingMoreConversations"
        :has-more-conversations="hasMoreConversations"
        :selected-conversation-id="selectedConversationId"
        @load-more-conversations="handleLoadMoreConversations"
        @select-conversation="handleSelectConversation"
      />
    </div>

    <div class="sidebar-footer">
      <!-- <template v-if="isAuthenticated">
        <button data-bs-toggle="modal" data-bs-target="#profileModal">Perfil</button>
        <button data-bs-toggle="modal" data-bs-target="#settingsModal">Configurações</button>
      </template> -->
      <button v-if="!isAuthenticated" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">Entrar</button>
      <button v-if="isAuthenticated" class="btn btn-outline-light btn-sm" @click="handleLogout">Sair</button>
      <!-- <button v-if="!isAuthenticated" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Criar conta</button> -->
    </div>
  </aside>
</template>

<style scoped>
/* Estilos da sidebar */
</style>
