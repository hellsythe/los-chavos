import { createApp } from 'vue'
import ChatbotConfig from './components/ChatbotConfig/Index.vue';

let element = document.getElementById('chatbot-config')
if (element !== null) {
    const app = createApp({});
    app.component('ChatbotConfig', ChatbotConfig)
    app.mount('#chatbot-config');
}
