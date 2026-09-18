<template>
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
</template>

<script>
export default {}
</script>

<style scoped>
/* Estilos do modal de registro */
</style>