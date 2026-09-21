<script>
export default {
  props: {
    isAuthenticated: { type: Boolean, default: false },
    sendingMessage: { type: Boolean, default: false },
  },
  emits: ['focus', 'send'],
  data() {
    return { promptText: '' }
  },
  methods: {
    emitFocus() { this.$emit('focus') },
    emitSend() {
      if (!this.promptText.trim()) return
      const content = this.promptText
      this.promptText = ''
      this.$emit('send', content)
    },
  },
}
</script>

<template>
  <div class="prompt-container">
    <div class="prompt-box">
      <textarea
        v-model="promptText"
        :readonly="!isAuthenticated"
        placeholder="Digite seu prompt aqui..."
        @focus="emitFocus"
        @keydown.enter.prevent="emitSend"
      ></textarea>
      <div class="prompt-actions justify-content-end">
        <button
          class="send-btn"
          :disabled="sendingMessage || !isAuthenticated"
          @click="emitSend"
        >
          Enviar
        </button>
      </div>
    </div>
  </div>
</template>
