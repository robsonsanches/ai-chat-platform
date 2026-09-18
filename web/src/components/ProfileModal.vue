<template>
  <!-- Modal Perfil -->
  <div class="modal fade" id="profileModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header border-0">
                  <h5 class="modal-title">Perfil</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                  <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" class="form-control" v-model="localUserProfile.name">
                  </div>

                  <div class="mb-3">
                      <label class="form-label">E-mail</label>
                      <input type="email" class="form-control" v-model="localUserProfile.email" readonly>
                  </div>

              </div>

              <div class="modal-footer border-0">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-success" @click="saveProfile">Salvar</button>
              </div>

          </div>
      </div>
  </div>
</template>

<script>
export default {
  props: {
    userProfile: { type: Object, required: true },
  },
  emits: ['profile-updated'],
  data() {
    return { localUserProfile: { ...this.userProfile } }
  },
  watch: {
    userProfile(newProfile) {
      this.localUserProfile = { ...newProfile }
    },
  },
  methods: {
    async saveProfile() {
  try {
    // Lógica para salvar o perfil na API
    // Exemplo: await api.put('/auth/profile', this.localUserProfile);
    console.log('Salvando perfil:', this.localUserProfile)
    this.$emit('profile-updated', this.localUserProfile)
    // Fechar modal após salvar, se necessário
    const modalElement = document.getElementById('profileModal')
    if (modalElement) {
      modalElement.querySelector('.btn-close').click() // Simula clique no botão fechar
    }
  } catch (err) {
    console.error('Erro ao salvar perfil:', err)
    // Lidar com erro
  }
    },
  },
}
</script>

<style scoped>
/* Estilos do modal de perfil */
</style>