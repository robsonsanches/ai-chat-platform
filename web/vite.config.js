import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const enableVueDevTools = env.VITE_ENABLE_VUE_DEVTOOLS === 'true'

  return {
    plugins: [
      vue(),
      ...(enableVueDevTools ? [vueDevTools()] : []),
    ],
  }
})
