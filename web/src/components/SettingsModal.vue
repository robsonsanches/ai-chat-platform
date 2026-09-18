<template>
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
</template>

<script>
export default {}
</script>

<style scoped>
/* Estilos do modal de configurações */
</style>