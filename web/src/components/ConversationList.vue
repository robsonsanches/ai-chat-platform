<template>
  <div class="history-list" @scroll="handleHistoryScroll">
      <button
          v-for="conversation in conversations"
          :key="conversation.id"
          type="button"
          class="prompt-item w-100 text-start"
          :class="{ 'active': selectedConversationId === conversation.id }"
          @click="selectConversation(conversation.id)"
      >
          {{ conversation.title }}
      </button>

      <div v-if="loadingMoreConversations" class="small text-secondary mt-2">
          Carregando mais conversas...
      </div>
  </div>
</template>

<script>
export default {
  props: {
    conversations: { type: Array, default: () => [] },
    loadingMoreConversations: { type: Boolean, default: false },
    hasMoreConversations: { type: Boolean, default: false },
    selectedConversationId: { type: Number, default: null },
  },
  emits: ['load-more-conversations', 'select-conversation'],
  methods: {
    handleHistoryScroll(event) {
  const element = event.target
  const threshold = 80

  if (this.loadingMoreConversations || !this.hasMoreConversations) {
    return
  }

  const nearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - threshold

  if (nearBottom) {
    this.$emit('load-more-conversations')
  }
    },
    selectConversation(conversationId) {
      this.$emit('select-conversation', conversationId)
    },
  },
}
</script>

<style scoped>
/* Estilos da lista de conversas */
</style>