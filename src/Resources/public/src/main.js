import './assets/main.css'


import { createApp } from 'vue/dist/vue.esm-bundler.js'
import { createPinia } from 'pinia'
import App from './App.vue'

const n8nManager = createApp(App)

n8nManager.use(createPinia())

n8nManager.mount('#n8nManager')
