import { createApp } from 'vue';
import KioskApp from './components/Kiosk/KioskApp.vue';

const mount = document.getElementById('kiosk-app');
if (mount) {
    const app = createApp(KioskApp);
    app.mount('#kiosk-app');
}
