<script>
import { Modal } from 'bootstrap'
import { APP_NAME, APP_SUBTITLE } from './config/app'
import api from './services/api'
import ChatArea from './components/ChatArea.vue'
import LoginModal from './components/LoginModal.vue'
import ProfileModal from './components/ProfileModal.vue'
import PromptBox from './components/PromptBox.vue'
import RegisterModal from './components/RegisterModal.vue'
import SettingsModal from './components/SettingsModal.vue'
import AppSidebar from './components/AppSidebar.vue'

export default {
  components: {
    AppSidebar,
    ChatArea,
    PromptBox,
    ProfileModal,
    SettingsModal,
    LoginModal,
    RegisterModal,
  },
  data() {
    return {
      APP_NAME,
      APP_SUBTITLE,
      isAuthenticated: false,
      selectedConversationId: null,
      sendingMessage: false,
      userProfile: { name: '', email: '' },
    }
  },
  methods: {
    async checkAuth() {
      if (!localStorage.getItem('access_token')) return
      try {
        await api.get('/auth/me')
        this.isAuthenticated = true
      } catch (err) {
        console.error('Token inválido ou expirado:', err)
        this.clearSession()
      }
    },
    clearSession() {
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      this.isAuthenticated = false
      this.selectedConversationId = null
    },
    async handleAuthenticated(status) {
      this.isAuthenticated = status
      if (status) await this.loadUserProfile()
    },
    async loadUserProfile() {
      try {
        const response = await api.get('/auth/me')
        this.userProfile = response.data
      } catch (err) {
        console.error('Erro ao carregar perfil do usuário:', err)
      }
    },
    openLoginModal() {
      if (this.isAuthenticated) return
      const modalElement = document.getElementById('loginModal')
      if (modalElement) Modal.getOrCreateInstance(modalElement).show()
    },
    handleNewChat() {
      this.selectedConversationId = null
      this.$refs.chatArea?.resetConversation()
    },
    handleConversationSelected(conversationId) {
      this.selectedConversationId = conversationId
    },
    async handleConversationCreated(conversationId) {
      this.selectedConversationId = conversationId
      await this.$refs.sidebar?.loadConversations()
    },
    handleSendMessage(content) {
      if (!this.isAuthenticated) {
        this.openLoginModal()
        return
      }
      this.$refs.chatArea?.sendMessage(content)
    },
    handleSendingChange(sending) {
      this.sendingMessage = sending
    },
    async handleLogout() {
      try {
        const response = await api.post('/auth/logout')
        if (response.data?.status === 'success') this.clearSession()
      } catch (err) {
        console.error('Erro ao fazer logout:', err)
      }
    },
    async handleRegistered(data) {
      if (!data?.access_token) return

      localStorage.setItem('access_token', data.access_token)
      if (data.user) localStorage.setItem('user', JSON.stringify(data.user))

      await this.handleAuthenticated(true)
      await this.$refs.sidebar?.loadConversations()
    },
  },
  async mounted() {
    await this.checkAuth()
  },
}
</script>

<template>
  <div class="app-container">
    <AppSidebar
      ref="sidebar"
      :is-authenticated="isAuthenticated"
      @start-new-chat="handleNewChat"
      @logout="handleLogout"
      @conversation-selected="handleConversationSelected"
    />

    <main class="main-content">
      <div class="topbar">
        <h5 class="mb-0">{{ APP_SUBTITLE }}</h5>
        <div class="text-secondary">{{ APP_NAME }}</div>
      </div>
      <ChatArea
        ref="chatArea"
        :is-authenticated="isAuthenticated"
        :selected-conversation-id="selectedConversationId"
        @conversation-created="handleConversationCreated"
        @sending-change="handleSendingChange"
      />
    </main>
  </div>

  <PromptBox
    :is-authenticated="isAuthenticated"
    :sending-message="sendingMessage"
    @focus="openLoginModal"
    @send="handleSendMessage"
  />
  <ProfileModal :user-profile="userProfile" @profile-updated="loadUserProfile" />
  <SettingsModal />
  <LoginModal @authenticated="handleAuthenticated" />
  <RegisterModal @registered="handleRegistered" />
</template>
