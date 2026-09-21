<script>
import api from '../services/api'

export default {
  props: {
    isAuthenticated: { type: Boolean, default: false },
    selectedConversationId: { type: Number, default: null },
  },
  emits: ['conversation-created', 'sending-change'],
  data() {
    return {
      loading: false,
      error: '',
      messages: [],
      sendingMessage: false,
      loadingMoreMessages: false,
      hasMoreMessages: false,
      messagePage: 1,
      messageLastPage: 1,
    }
  },
  watch: {
    isAuthenticated(authenticated) {
      if (!authenticated) this.resetConversation()
    },
    selectedConversationId(conversationId) {
      if (conversationId) this.loadConversationMessages(conversationId)
      else this.resetConversation()
    },
    'messages.length': 'scrollToBottom',
    sendingMessage(sending) {
      this.$emit('sending-change', sending)
      if (sending) this.scrollToBottom()
    },
  },
  methods: {
    resetConversation() {
      this.messages = []
      this.error = ''
      this.messagePage = 1
      this.messageLastPage = 1
      this.hasMoreMessages = false
      this.loadingMoreMessages = false
      this.sendingMessage = false
    },
    async scrollToBottom() {
      await this.$nextTick()
      if (this.$refs.container) this.$refs.container.scrollTop = this.$refs.container.scrollHeight
    },
    handleScroll() {
      if (!this.hasMoreMessages || this.loadingMoreMessages || !this.selectedConversationId) return
      const element = this.$refs.container
      const nearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 80
      if (nearBottom) this.loadConversationMessages(this.selectedConversationId, this.messagePage + 1, true)
    },
    async loadConversationMessages(conversationId, page = 1, append = false) {
      if (!conversationId || (page > 1 && (this.loadingMoreMessages || !this.hasMoreMessages))) return
      if (page === 1) {
        this.loading = true
        this.messages = []
        this.messagePage = 1
        this.messageLastPage = 1
        this.hasMoreMessages = false
      } else {
        this.loadingMoreMessages = true
      }
      this.error = ''

      try {
        const response = await api.get(`/conversations/${conversationId}/messages`, { params: { page } })
        const payload = response.data?.data
        const items = Array.isArray(payload?.data) ? payload.data : []
        this.messages = append ? [...this.messages, ...items] : items
        this.messagePage = payload?.current_page || page
        this.messageLastPage = payload?.last_page || 1
        this.hasMoreMessages = this.messagePage < this.messageLastPage
      } catch (err) {
        this.error = 'Não foi possível carregar as mensagens da conversa.'
        this.messages = []
        console.error(err)
      } finally {
        if (page === 1) this.loading = false
        else this.loadingMoreMessages = false
      }
    },
    async sendMessage(content) {
      if (!this.isAuthenticated || !content?.trim() || this.sendingMessage) return
      const messageContent = content.trim()
      this.sendingMessage = true
      this.error = ''
      const payload = { message: { content: messageContent } }
      if (this.selectedConversationId) payload.conversation_id = this.selectedConversationId
      this.messages = [...this.messages, { id: `local-${Date.now()}`, role: 'user', content: messageContent }]

      try {
        const response = await api.post('/conversations', payload)
        const conversationId = response.data?.data?.conversation_id || this.selectedConversationId
        const assistantMessage = response.data?.data?.message
        if (assistantMessage) this.messages = [...this.messages, assistantMessage]
        if (conversationId) this.$emit('conversation-created', conversationId)
      } catch (err) {
        this.error = 'Não foi possível enviar a mensagem.'
        console.error(err)
      } finally {
        this.sendingMessage = false
      }
    },
  },
}
</script>

<template>
  <div ref="container" class="chat-area" @scroll="handleScroll">
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
        <div v-if="sendingMessage" class="small text-secondary mt-2">Pensando ...</div>
        <div v-if="loadingMoreMessages" class="small text-secondary mt-2">Carregando mais mensagens...</div>
      </template>
    </div>
  </div>
</template>
