import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import { APP_TITLE } from './config/app'

const app = createApp(App)

app.mount('#app')

document.title = APP_TITLE
